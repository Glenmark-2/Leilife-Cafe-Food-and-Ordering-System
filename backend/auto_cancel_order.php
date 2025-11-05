<?php
require_once __DIR__ . '/db_script/db.php';
require_once __DIR__ . '/db_script/appData.php';
require_once __DIR__ . '/create_payment_intent.php'; // ✅ include your helper
session_start();

header('Content-Type: application/json');

$order_number = $_POST['order_number'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

$response = [
    'success' => false,
    'message' => '',
    'debug' => []
];

// Basic validation
if (!$order_number || !$user_id) {
    $response['message'] = 'Invalid request';
    echo json_encode($response);
    exit;
}

try {
    // 1️⃣ Get the order
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = :onum AND user_id = :uid");
    $stmt->execute([':onum' => $order_number, ':uid' => $user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception('Order not found or unauthorized.');
    }

    $orderId = $order['order_id'];
    $response['debug'][] = "Found order_id={$orderId}, payment_status={$order['payment_status']}, payment_method={$order['payment_method']}";

    // 2️⃣ Start DB transaction
    $pdo->beginTransaction();

    // 3️⃣ Cancel the order and its items
    $pdo->prepare("UPDATE orders SET status='cancelled' WHERE order_id=:oid")
        ->execute([':oid' => $orderId]);

    $pdo->prepare("UPDATE order_items SET status='cancelled' WHERE order_id=:oid")
        ->execute([':oid' => $orderId]);

    $response['debug'][] = "Order + items marked as cancelled.";

    // 4️⃣ If GCash and paid → issue refund using your helper
    if ($order['payment_method'] === 'gcash' && $order['payment_status'] === 'paid') {
        $response['debug'][] = "Eligible for refund. Triggering createRefund()...";

        // Use your shared helper
        $refundData = createRefund(
            $order['payment_id'],
            $order['total'],
            $orderId,
            null, // item_id since cancelling entire order
            "requested_by_customer"
        );

        $refundId     = $refundData['data']['id'] ?? null;
        $refundStatus = $refundData['data']['attributes']['status'] ?? 'pending';
        $refundAmount = $refundData['data']['attributes']['amount'] ?? 0;

        // Insert refund log
        $pdo->prepare("INSERT INTO refunds (order_id, order_item_id, transaction_id, payment_id, paymongo_refund_id, amount, reason, status)
                       VALUES (:oid, NULL, NULL, :pid, :pmrid, :amt, 'User cancelled order', :st)")
            ->execute([
                ':oid' => $orderId,
                ':pid' => $order['payment_id'],
                ':pmrid' => $refundId,
                ':amt' => $refundAmount / 100,
                ':st' => $refundStatus
            ]);

        // Log transaction entry
        $pdo->prepare("INSERT INTO transactions (order_id, external_id, transaction_name, transaction_status, transaction_value)
                       VALUES (:oid, :ext, 'refund', :st, :val)")
            ->execute([
                ':oid' => $orderId,
                ':ext' => $refundId,
                ':st'  => ($refundStatus === 'succeeded' ? 'success' : ($refundStatus === 'failed' ? 'failed' : 'pending')),
                ':val' => $refundAmount / 100
            ]);

        // Update order payment_status
        $pdo->prepare("UPDATE orders SET payment_status='refunded' WHERE order_id=:oid")
            ->execute([':oid' => $orderId]);

        $response['debug'][] = "Refund logged. refund_id={$refundId}, status={$refundStatus}, amount=" . ($refundAmount/100);
    }

    // 5️⃣ Commit transaction
    $pdo->commit();

    $response['success'] = true;
    $response['message'] = 'Order cancelled successfully.';
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();

    $response['message'] = 'Failed to cancel order.';
    $response['debug'][] = $e->getMessage();

    file_put_contents(__DIR__ . "/auto_cancel_error.log",
        date("Y-m-d H:i:s") . " " . $e->getMessage() . PHP_EOL,
        FILE_APPEND
    );
}

echo json_encode($response);
