<?php
// paymongo_webhook.php
require_once __DIR__ . '/db_script/db.php';
header("Content-Type: application/json");

$rawPayload = file_get_contents("php://input");
$event = json_decode($rawPayload, true);

// Log raw payload
file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " RAW: " . $rawPayload . PHP_EOL, FILE_APPEND);

if (!$event || !isset($event['data'])) {
    http_response_code(400);
    file_put_contents(__DIR__ . "/paymongo_webhook_error.log", date("Y-m-d H:i:s") . " Invalid JSON payload\n", FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Invalid webhook payload"]);
    exit;
}

// Standardize shapes
$topType = $event['data']['type'] ?? null;
$eventType = null;
$inner = [];

// If PayMongo wrapped event (typical), event type sits in attributes.type and payload attributes data contains the resource
if ($topType === 'event') {
    $eventType = $event['data']['attributes']['type'] ?? null;
    $inner = $event['data']['attributes']['data']['attributes'] ?? [];
    file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " TopType=event, EventType={$eventType}" . PHP_EOL, FILE_APPEND);
} else {
    $eventType = $topType;
    $inner = $event['data']['attributes'] ?? [];
    file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " TopType={$topType}, EventType={$eventType}" . PHP_EOL, FILE_APPEND);
}

// fallback safety
if (empty($inner) && isset($event['data']['attributes']['data']['attributes'])) {
    $inner = $event['data']['attributes']['data']['attributes'];
}

// extract metadata (order_id usually in metadata)
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

// payment id extraction (may be in different places)
$paymentId = $inner['id'] 
    ?? ($event['data']['attributes']['data']['id'] ?? null)
    ?? ($event['data']['id'] ?? null)
    ?? null;

// classify event
$eventTypeLower = strtolower($eventType ?? '');
$statusLower = strtolower($status ?? '');
$paidStatuses = ['paid', 'succeeded'];
$failedStatuses = ['failed', 'cancelled', 'canceled'];

$isRefund = in_array($eventTypeLower, [
    'refund.succeeded',
    'refund.created',
    'payment.refunded',
    'payment.refund.updated',
    'refund'
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

    // If we need to act on an order-level change, ensure order exists to avoid FK violation
    if (($isSuccess || $isFailure || $isRefund) && !empty($orderId)) {
        $checkOrder = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE order_id = :oid");
        $checkOrder->execute([':oid' => $orderId]);
        if ($checkOrder->fetchColumn() == 0) {
            // Order not present yet — skip and commit (avoid FK errors)
            file_put_contents(__DIR__ . "/paymongo_webhook_error.log",
                date("Y-m-d H:i:s") . " Skipping event: order_id {$orderId} not found\n",
                FILE_APPEND
            );
            $pdo->commit();
            http_response_code(200);
            echo json_encode(['success' => true, 'skipped' => true]);
            exit;
        }
    }

    // --- PAYMENT SUCCESS ---
    if ($isSuccess) {
        $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'paid', payment_id = :pid WHERE order_id = :oid");
        $stmt->execute([':pid' => $paymentId, ':oid' => $orderId]);

        // clear cart items (your existing behavior)
        $pdo->prepare("DELETE ci FROM cart_items ci
                       JOIN carts c ON ci.cart_id = c.cart_id
                       JOIN orders o ON o.user_id = c.user_id
                       WHERE o.order_id = :oid")->execute([':oid' => $orderId]);

        // Insert payment txn if not exists (dedupe by payment external id)
        $chk = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE external_id = :ext AND transaction_name = 'payment'");
        $chk->execute([':ext' => $paymentId]);
        if ($chk->fetchColumn() == 0) {
            $txn = $pdo->prepare("INSERT INTO transactions (order_id, external_id, transaction_name, transaction_status, transaction_value)
                                  VALUES (:oid, :ext, 'payment', 'success', :val)");
            $txn->execute([
                ':oid' => $orderId,
                ':ext' => $paymentId,
                ':val' => ($inner['amount'] ?? 0) / 100
            ]);
        } else {
            file_put_contents(__DIR__ . "/paymongo_webhook.log",
                date("Y-m-d H:i:s") . " Payment transaction already exists for external_id={$paymentId}\n",
                FILE_APPEND);
        }

        file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " ✅ Order {$orderId} marked paid\n", FILE_APPEND);

    // --- PAYMENT FAILURE ---
    } elseif ($isFailure) {
        $pdo->prepare("UPDATE orders SET payment_status = 'failed' WHERE order_id = :oid")->execute([':oid' => $orderId]);

        $chk = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE external_id = :ext AND transaction_name = 'payment'");
        $chk->execute([':ext' => $paymentId]);
        if ($chk->fetchColumn() == 0) {
            $txn = $pdo->prepare("INSERT INTO transactions (order_id, external_id, transaction_name, transaction_status, transaction_value)
                                  VALUES (:oid, :ext, 'payment', 'failed', 0)");
            $txn->execute([':oid' => $orderId, ':ext' => $paymentId]);
        }

        file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " ❌ Order {$orderId} failed\n", FILE_APPEND);

    // --- REFUND EVENTS ---
    } elseif ($isRefund) {
        // Build normalized refund list — ALWAYS use the refund resource id (ref_...) as canonical id
        $normalized = [];

        // Case 1: event is payment.refunded (payment object contains refunds[] with correct ref IDs)
        if (!empty($inner['refunds']) && is_array($inner['refunds'])) {
            foreach ($inner['refunds'] as $r) {
                // r may be array with id and attributes
                $rid = $r['id'] ?? ($r['attributes']['id'] ?? null);
                $attrs = $r['attributes'] ?? [];
                $normalized[] = [
                    'id' => $rid,
                    'amount' => $attrs['amount'] ?? 0,
                    'status' => $attrs['status'] ?? null,
                    'payment_id' => $attrs['payment_id'] ?? $paymentId,
                    'item_id' => $attrs['metadata']['item_id'] ?? null
                ];
            }
        } else {
            // Case 2: refund-specific or mixed event
            $refundResource =
                $event['data']['attributes']['data'] ??
                $event['data']['attributes'] ??
                $event['data'] ?? null;

            if (is_array($refundResource)) {
                // Try to detect the actual refund payload
                $rid = $refundResource['id'] ?? ($refundResource['attributes']['id'] ?? null);
                $attrs = $refundResource['attributes'] ?? $refundResource;

                // Safety: sometimes id is at top level
                if (!empty($rid) && strpos($rid, 'ref_') === 0) {
                    $normalized[] = [
                        'id' => $rid,
                        'amount' => $attrs['amount'] ?? 0,
                        'status' => $attrs['status'] ?? null,
                        'payment_id' => $attrs['payment_id'] ?? $paymentId,
                        'item_id' => $attrs['metadata']['item_id'] ?? null
                    ];
                }
            }

            // As last resort, scan for a nested refund id anywhere in the payload
            if (empty($normalized)) {
                $allJson = json_encode($event);
                if (preg_match('/"id":"(ref_[a-zA-Z0-9_]+)"/', $allJson, $m)) {
                    $rid = $m[1];
                    $normalized[] = [
                        'id' => $rid,
                        'amount' => $inner['amount'] ?? 0,
                        'status' => $inner['status'] ?? null,
                        'payment_id' => $inner['payment_id'] ?? $paymentId,
                        'item_id' => $inner['metadata']['item_id'] ?? null
                    ];
                }
            }
        }

        // Process normalized refunds (dedupe by refund resource id)
        foreach ($normalized as $rf) {
            $refundId = $rf['id'] ?? null;
            if (empty($refundId)) continue;

            $refundAmount = intval($rf['amount'] ?? 0);
            $refundStatus = $rf['status'] ?? null;
            $refPaymentId = $rf['payment_id'] ?? $paymentId;
            $itemId = $rf['item_id'] ?? null;

            file_put_contents(__DIR__ . "/paymongo_webhook.log",
                date("Y-m-d H:i:s") . " Normalized refund: id={$refundId}, amount={$refundAmount}, status={$refundStatus}\n", FILE_APPEND);

            // Check if refund already recorded in refunds table
            $chkRefund = $pdo->prepare("SELECT refund_id FROM refunds WHERE paymongo_refund_id = :pmrid LIMIT 1");
            $chkRefund->execute([':pmrid' => $refundId]);
            $existing = $chkRefund->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // If already exists, update status/amount if changed (do NOT insert duplicate)
                $upd = $pdo->prepare("UPDATE refunds SET amount = :amt, status = :st WHERE paymongo_refund_id = :pmrid");
                $upd->execute([
                    ':amt' => ($refundAmount / 100),
                    ':st' => $refundStatus,
                    ':pmrid' => $refundId
                ]);
                file_put_contents(__DIR__ . "/paymongo_webhook.log",
                    date("Y-m-d H:i:s") . " Updated existing refund record for {$refundId}\n", FILE_APPEND);
            } else {
                // Insert new refund record
                $ins = $pdo->prepare("INSERT INTO refunds (order_id, order_item_id, transaction_id, payment_id, paymongo_refund_id, amount, reason, status)
                                      VALUES (:oid, :iid, NULL, :pid, :pmrid, :amt, 'Provider refund', :st)");
                $ins->execute([
                    ':oid' => $orderId,
                    ':iid' => $itemId,
                    ':pid' => $refPaymentId,
                    ':pmrid' => $refundId,
                    ':amt' => ($refundAmount / 100),
                    ':st' => $refundStatus
                ]);
                file_put_contents(__DIR__ . "/paymongo_webhook.log",
                    date("Y-m-d H:i:s") . " Inserted refunds row for {$refundId}\n", FILE_APPEND);
            }

            // Next: ensure only one transaction row for this refund external_id
            $chkTxn = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE external_id = :ext AND transaction_name = 'refund'");
            $chkTxn->execute([':ext' => $refundId]);
            if ($chkTxn->fetchColumn() > 0) {
                file_put_contents(__DIR__ . "/paymongo_webhook.log",
                    date("Y-m-d H:i:s") . " Skipping transaction insert for already-logged refund {$refundId}\n", FILE_APPEND);
            } else {
                $txnStatus = ($refundStatus === 'succeeded' || $refundStatus === 'success') ? 'success' : (($refundStatus === 'failed') ? 'failed' : 'pending');

                $logTxn = $pdo->prepare("INSERT INTO transactions (order_id, external_id, transaction_name, transaction_status, transaction_value)
                                         VALUES (:oid, :ext, 'refund', :st, :val)");
                $logTxn->execute([
                    ':oid' => $orderId,
                    ':ext' => $refundId,
                    ':st'  => $txnStatus,
                    ':val' => ($refundAmount / 100)
                ]);
                file_put_contents(__DIR__ . "/paymongo_webhook.log",
                    date("Y-m-d H:i:s") . " Inserted transaction row for refund {$refundId}\n", FILE_APPEND);
            }

            // Cancel specific item if provided
            if (!empty($itemId)) {
                $pdo->prepare("UPDATE order_items SET status='cancelled' WHERE order_item_id=:iid AND order_id=:oid")
                    ->execute([':iid' => $itemId, ':oid' => $orderId]);
            }

            // If all items cancelled, mark order refunded
            $check = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE order_id=:oid AND status!='cancelled'");
            $check->execute([':oid' => $orderId]);
            if ($check->fetchColumn() == 0) {
                $pdo->prepare("UPDATE orders SET status='cancelled', payment_status='refunded' WHERE order_id=:oid")
                    ->execute([':oid' => $orderId]);
            }
        } // foreach normalized refunds

        file_put_contents(__DIR__ . "/paymongo_webhook.log",
            date("Y-m-d H:i:s") . " Completed refund processing for order {$orderId}\n", FILE_APPEND);
    } else {
        file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " Ignored non-final event {$eventType}\n", FILE_APPEND);
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
