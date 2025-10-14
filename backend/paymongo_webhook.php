<?php
// paymongo_webhook.php
require_once __DIR__ . '/db_script/db.php';

header("Content-Type: application/json");

$rawPayload = file_get_contents("php://input");
$event = json_decode($rawPayload, true);

// Always log the full payload (timestamp + payload)
file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " RAW: " . $rawPayload . PHP_EOL, FILE_APPEND);

// Basic validation
if (!$event || !isset($event['data'])) {
    http_response_code(400);
    file_put_contents(__DIR__ . "/paymongo_webhook_error.log", date("Y-m-d H:i:s") . " Invalid or empty JSON payload\n", FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Invalid webhook payload"]);
    exit;
}

// Normalize / extract metadata/status/event
$topType = $event['data']['type'] ?? null; // could be 'event', 'payment_intent', 'payment'
$eventType = null;    // e.g. "payment.paid" or "payment_intent.succeeded"
$inner = [];          // the payment or payment_intent attributes array

// Case: wrapped event (common webhook shape)
if ($topType === 'event') {
    $eventType = $event['data']['attributes']['type'] ?? null; // ex: "payment.paid"
    $inner = $event['data']['attributes']['data']['attributes'] ?? [];
    file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " TopType=event, EventType={$eventType}" . PHP_EOL, FILE_APPEND);
} else {
    // Direct object shape: data.type == "payment_intent" or "payment"
    $eventType = $topType; 
    $inner = $event['data']['attributes'] ?? [];
    file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " TopType={$topType}, EventType={$eventType}" . PHP_EOL, FILE_APPEND);
}

// Try more places just in case
if (empty($inner)) {
    // try a couple fallbacks
    $inner = $event['data']['attributes'] ?? [];
    if (empty($inner) && isset($event['data']['attributes']['data']['attributes'])) {
        $inner = $event['data']['attributes']['data']['attributes'];
    }
    file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " Used fallback for inner attributes" . PHP_EOL, FILE_APPEND);
}

// metadata may sit in different places; pick first found
$metadataCandidates = [
    $inner['metadata'] ?? null,
    $event['data']['attributes']['metadata'] ?? null,
    $event['data']['attributes']['data']['attributes']['metadata'] ?? null
];

$metadata = null;
foreach ($metadataCandidates as $m) {
    if (is_array($m) && !empty($m)) { 
        $metadata = $m; 
        break; 
    }
}

$orderId = null;
if (is_array($metadata)) {
    $orderId = $metadata['order_id'] ?? $metadata['reference_number'] ?? null;
}
file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " Extracted orderId=" . ($orderId ?? 'NULL') . PHP_EOL, FILE_APPEND);

// status extracted from inner
$status = $inner['status'] ?? null; // e.g. "paid" or "succeeded"
file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " Extracted status=" . ($status ?? 'NULL') . PHP_EOL, FILE_APPEND);

// ----------------------
// Robust payment id extraction:
// prefer resource id (pay_xxx) from inner or attributes.data.id,
// fall back to event data id (evt_xxx) only if necessary.
// ----------------------
$paymentId = null;
// common: inner['id'] holds pay_xxx for direct payment object
if (!empty($inner['id'])) {
    $paymentId = $inner['id'];
}
// wrapped event: attributes.data.id often contains pay_xxx
elseif (!empty($event['data']['attributes']['data']['id'])) {
    $paymentId = $event['data']['attributes']['data']['id'];
}
// last resort: event id (evt_xxx) — keep for logging but avoid using for refunds
elseif (!empty($event['data']['id'])) {
    $paymentId = $event['data']['id'];
}

file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " Extracted paymentId=" . ($paymentId ?? 'NULL') . PHP_EOL, FILE_APPEND);

// Decide success/failure/refund robustly
$paidStatuses = ['paid', 'succeeded'];
$failedStatuses = ['failed', 'cancelled', 'canceled'];

$isSuccess = in_array(strtolower($status ?? ''), $paidStatuses)
             || in_array(strtolower($eventType ?? ''), ['payment.paid', 'payment_intent.succeeded']);

$isFailure = in_array(strtolower($status ?? ''), $failedStatuses)
             || in_array(strtolower($eventType ?? ''), ['payment.failed', 'payment_intent.payment_failed']);

$isRefund = in_array(strtolower($eventType ?? ''), ['refund.succeeded', 'refund.created']);

file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " Condition check: isSuccess=" . ($isSuccess ? 'yes' : 'no') . ", isFailure=" . ($isFailure ? 'yes' : 'no'). ", isRefund=" . ($isRefund ? 'yes' : 'no') . PHP_EOL, FILE_APPEND);

// Update DB accordingly
try {
    $pdo->beginTransaction();

    if ($isSuccess) {
        // Use the robust $paymentId (prefer pay_xxx); store it in orders.payment_id
        $stmt = $pdo->prepare("UPDATE orders 
                               SET payment_status = 'paid', payment_id = :pid 
                               WHERE order_id = :oid");
        $stmt->execute([':pid' => $paymentId, ':oid' => $orderId]);

        // Clear cart (unchanged)
        $clearCartStmt = $pdo->prepare("
            DELETE ci FROM cart_items ci
            JOIN carts c ON ci.cart_id = c.cart_id
            JOIN orders o ON o.user_id = c.user_id
            WHERE o.order_id = :oid
        ");
        $clearCartStmt->execute([':oid' => $orderId]);

        // Also log transaction with external_id = payment resource id (preferred)
        $logTxn = $pdo->prepare("INSERT INTO transactions 
                                 (order_id, external_id, transaction_name, transaction_status, transaction_value)
                                 VALUES (:oid, :ext, 'payment', 'success', :val)");
        $logTxn->execute([
            ':oid' => $orderId,
            ':ext' => $paymentId,
            ':val' => ($inner['amount'] ?? 0) / 100
        ]);

        file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " ✅ Order {$orderId} marked paid and cart cleared (paymentId={$paymentId})" . PHP_EOL, FILE_APPEND);

    } elseif ($isFailure) {
        $stmt = $pdo->prepare("UPDATE orders 
                               SET payment_status = 'failed' 
                               WHERE order_id = :oid");
        $stmt->execute([':oid' => $orderId]);

        $txn = $pdo->prepare("INSERT INTO transactions (order_id, external_id, transaction_name, transaction_status, transaction_value)
                              VALUES (:oid, :ext, 'payment', 'failed', 0)");
        $txn->execute([
            ':oid' => $orderId,
            ':ext' => $paymentId
        ]);

        file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " ❌ Order {$orderId} marked failed (paymentId={$paymentId})" . PHP_EOL, FILE_APPEND);

    } elseif ($isRefund) {
        // Refund handling (unchanged logic) — amount is in centavos in $inner['amount']
        $refundId = $event['data']['id'] ?? null;
        $refundAmount = $inner['amount'] ?? null; // in centavos
        $refundStatus = $inner['status'] ?? null;
        // payment_id inside refund object (resource that was refunded)
        $refPaymentId = $inner['payment_id'] ?? $paymentId;

        $itemId = $metadata['item_id'] ?? null;

        // Always log refund details into refunds table
        $logRefund = $pdo->prepare("INSERT INTO refunds (order_id, refund_id, payment_id, amount, status, created_at)
                                    VALUES (:oid, :rid, :pid, :amt, :st, NOW())");
        $logRefund->execute([
            ':oid' => $orderId,
            ':rid' => $refundId,
            ':pid' => $refPaymentId,
            ':amt' => ($refundAmount / 100),
            ':st'  => $refundStatus
        ]);

        // Also log into transactions ledger
        $logTxn = $pdo->prepare("INSERT INTO transactions (order_id, external_id, transaction_name, transaction_status, transaction_value)
                                 VALUES (:oid, :ext, 'refund', :st, :amt)");
        $logTxn->execute([
            ':oid' => $orderId,
            ':ext' => $refundId,
            ':st'  => $refundStatus,
            ':amt' => ($refundAmount / 100)
        ]);

        // If item_id provided in refund metadata, cancel only that item
        if (!empty($itemId)) {
            $upd = $pdo->prepare("UPDATE order_items 
                                  SET status = 'cancelled' 
                                  WHERE order_item_id = :iid AND order_id = :oid");
            $upd->execute([':iid' => $itemId, ':oid' => $orderId]);
        }

        // If all items cancelled, mark order cancelled + refunded
        $check = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE order_id = :oid AND status != 'cancelled'");
        $check->execute([':oid' => $orderId]);
        $remaining = (int)$check->fetchColumn();
        if ($remaining === 0) {
            $pdo->prepare("UPDATE orders SET status = 'cancelled', payment_status = 'refunded' WHERE order_id = :oid")
                ->execute([':oid' => $orderId]);
        }

        file_put_contents(__DIR__ . "/paymongo_webhook.log",
            date("Y-m-d H:i:s") . " 💸 Refund processed for order {$orderId}, itemId=" . ($itemId ?? 'NULL') . ", refundId=" . ($refundId ?? 'NULL') . ", amount=" . ($refundAmount !== null ? ($refundAmount/100) : 'NULL') . PHP_EOL,
            FILE_APPEND
        );
    } else {
        file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " ℹ️ Non-terminal webhook for order {$orderId}: eventType={$eventType}, status={$status}" . PHP_EOL, FILE_APPEND);
    }

    $pdo->commit();
    echo json_encode(["success" => true]);
    http_response_code(200);
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    file_put_contents(__DIR__ . "/paymongo_webhook_error.log", date("Y-m-d H:i:s") . " Exception: " . $e->getMessage() . PHP_EOL . "Payload: " . $rawPayload . PHP_EOL, FILE_APPEND);
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
    exit;
}
