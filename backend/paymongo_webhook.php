<?php
// paymongo_webhook.php
require_once __DIR__ . '/db_script/db.php';
header("Content-Type: application/json");

$rawPayload = file_get_contents("php://input");
$event = json_decode($rawPayload, true);

// Log all payloads for debugging
file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " RAW: " . $rawPayload . PHP_EOL, FILE_APPEND);

if (!$event || !isset($event['data'])) {
    http_response_code(400);
    file_put_contents(__DIR__ . "/paymongo_webhook_error.log", date("Y-m-d H:i:s") . " Invalid JSON payload\n", FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Invalid webhook payload"]);
    exit;
}

// Extract shape
$topType = $event['data']['type'] ?? null;
$eventType = null;
$inner = [];

if ($topType === 'event') {
    $eventType = $event['data']['attributes']['type'] ?? null;
    $inner = $event['data']['attributes']['data']['attributes'] ?? [];
    file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " TopType=event, EventType={$eventType}" . PHP_EOL, FILE_APPEND);
} else {
    $eventType = $topType;
    $inner = $event['data']['attributes'] ?? [];
    file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " TopType={$topType}, EventType={$eventType}" . PHP_EOL, FILE_APPEND);
}

if (empty($inner) && isset($event['data']['attributes']['data']['attributes'])) {
    $inner = $event['data']['attributes']['data']['attributes'];
}

// Extract metadata
$metadata = null;
$metadataCandidates = [
    $inner['metadata'] ?? null,
    $event['data']['attributes']['metadata'] ?? null,
    $event['data']['attributes']['data']['attributes']['metadata'] ?? null
];
foreach ($metadataCandidates as $m) {
    if (is_array($m) && !empty($m)) { $metadata = $m; break; }
}

$orderId = $metadata['order_id'] ?? null;
$status = $inner['status'] ?? null;
file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " Extracted orderId=" . ($orderId ?? 'NULL') . ", status=" . ($status ?? 'NULL') . PHP_EOL, FILE_APPEND);

// Extract paymentId safely
$paymentId = $inner['id'] 
    ?? $event['data']['attributes']['data']['id'] 
    ?? $event['data']['id'] 
    ?? null;

// Detect event type
$eventTypeLower = strtolower($eventType ?? '');
$statusLower = strtolower($status ?? '');
$paidStatuses = ['paid', 'succeeded'];
$failedStatuses = ['failed', 'cancelled', 'canceled'];

$isRefund = in_array($eventTypeLower, [
    'refund.succeeded', 'refund.created', 'payment.refunded', 'refund'
]);
$isSuccess = !$isRefund && (in_array($statusLower, $paidStatuses) || in_array($eventTypeLower, ['payment.paid', 'payment_intent.succeeded']));
$isFailure = !$isRefund && (in_array($statusLower, $failedStatuses) || in_array($eventTypeLower, ['payment.failed', 'payment_intent.payment_failed']));

file_put_contents(__DIR__ . "/paymongo_webhook.log",
    date("Y-m-d H:i:s") . " Condition check: isSuccess=" . ($isSuccess?'yes':'no') . 
    ", isFailure=" . ($isFailure?'yes':'no') . 
    ", isRefund=" . ($isRefund?'yes':'no') . PHP_EOL,
    FILE_APPEND
);

try {
    $pdo->beginTransaction();

    if ($isSuccess) {
        // --------------- PAYMENT SUCCESS -----------------
        $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'paid', payment_id = :pid WHERE order_id = :oid");
        $stmt->execute([':pid' => $paymentId, ':oid' => $orderId]);

        $pdo->prepare("DELETE ci FROM cart_items ci
                       JOIN carts c ON ci.cart_id = c.cart_id
                       JOIN orders o ON o.user_id = c.user_id
                       WHERE o.order_id = :oid")->execute([':oid' => $orderId]);

        $txn = $pdo->prepare("INSERT INTO transactions (order_id, external_id, transaction_name, transaction_status, transaction_value)
                              VALUES (:oid, :ext, 'payment', 'success', :val)");
        $txn->execute([
            ':oid' => $orderId,
            ':ext' => $paymentId,
            ':val' => ($inner['amount'] ?? 0) / 100
        ]);

        file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " ✅ Order {$orderId} marked paid\n", FILE_APPEND);

    } elseif ($isFailure) {
        // --------------- PAYMENT FAILED -----------------
        $pdo->prepare("UPDATE orders SET payment_status = 'failed' WHERE order_id = :oid")->execute([':oid' => $orderId]);

        $txn = $pdo->prepare("INSERT INTO transactions (order_id, external_id, transaction_name, transaction_status, transaction_value)
                              VALUES (:oid, :ext, 'payment', 'failed', 0)");
        $txn->execute([':oid' => $orderId, ':ext' => $paymentId]);

        file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " ❌ Order {$orderId} failed\n", FILE_APPEND);

    } elseif ($isRefund) {
        // --------------- REFUND EVENT -----------------
        $refundData = $inner['refunds'][0]['attributes'] ?? null;
        $refundResourceId = $inner['refunds'][0]['id'] ?? null;

        if ($refundData) {
            $refundId     = $refundResourceId;
            $refundAmount = $refundData['amount'] ?? 0;
            $refundStatus = $refundData['status'] ?? null;
            $refPaymentId = $refundData['payment_id'] ?? $paymentId;
            $itemId       = $refundData['metadata']['item_id'] ?? null;
        } else {
            $refundId     = $event['data']['id'] ?? null;
            $refundAmount = $inner['amount'] ?? 0;
            $refundStatus = $inner['status'] ?? null;
            $refPaymentId = $inner['payment_id'] ?? $paymentId;
            $itemId       = $metadata['item_id'] ?? null;
        }

        file_put_contents(__DIR__ . "/paymongo_webhook.log",
            date("Y-m-d H:i:s") . " Refund extracted: refundId={$refundId}, refundAmount={$refundAmount}, refundStatus={$refundStatus}" . PHP_EOL,
            FILE_APPEND
        );

        // Insert refund (NOTE: refunds.refund_id is AUTO_INCREMENT)
        $insertRefund = $pdo->prepare("INSERT INTO refunds (order_id, order_item_id, transaction_id, payment_id, paymongo_refund_id, amount, reason, status)
                                       VALUES (:oid, :iid, NULL, :pid, :pmrid, :amt, 'User requested refund', :st)");
        $insertRefund->execute([
            ':oid'   => $orderId,
            ':iid'   => $itemId,
            ':pid'   => $refPaymentId,
            ':pmrid' => $refundId,
            ':amt'   => ($refundAmount / 100),
            ':st'    => $refundStatus
        ]);

        // Log in transactions (ENUM only allows: success | failed | pending)
        $txnStatus = ($refundStatus === 'succeeded') ? 'success' : (($refundStatus === 'failed') ? 'failed' : 'pending');

        $logTxn = $pdo->prepare("INSERT INTO transactions (order_id, external_id, transaction_name, transaction_status, transaction_value)
                                 VALUES (:oid, :ext, 'refund', :st, :val)");
        $logTxn->execute([
            ':oid' => $orderId,
            ':ext' => $refundId,
            ':st'  => $txnStatus,
            ':val' => ($refundAmount / 100)
        ]);

        // Cancel items if needed
        if (!empty($itemId)) {
            $pdo->prepare("UPDATE order_items SET status='cancelled' WHERE order_item_id=:iid AND order_id=:oid")
                ->execute([':iid' => $itemId, ':oid' => $orderId]);
        }

        // Mark order as refunded if all items cancelled
        $check = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE order_id=:oid AND status!='cancelled'");
        $check->execute([':oid' => $orderId]);
        if ($check->fetchColumn() == 0) {
            $pdo->prepare("UPDATE orders SET status='cancelled', payment_status='refunded' WHERE order_id=:oid")
                ->execute([':oid' => $orderId]);
        }

        file_put_contents(__DIR__ . "/paymongo_webhook.log",
            date("Y-m-d H:i:s") . " 💸 Refund logged for order {$orderId} ({$refundAmount}/100 PHP)\n", FILE_APPEND);
    } else {
        file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " ℹ️ Ignored non-final event {$eventType}\n", FILE_APPEND);
    }

    $pdo->commit();
    http_response_code(200);
    echo json_encode(["success" => true]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    file_put_contents(__DIR__ . "/paymongo_webhook_error.log",
        date("Y-m-d H:i:s") . " Exception: " . $e->getMessage() . PHP_EOL . "Payload: " . $rawPayload . PHP_EOL,
        FILE_APPEND
    );
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
