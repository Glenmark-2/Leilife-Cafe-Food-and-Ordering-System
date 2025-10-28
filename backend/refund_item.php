<?php
require_once __DIR__ . '/db_script/db.php';
require_once __DIR__ . '/create_payment_intent.php'; // for createRefund() + getRemainingRefundable()
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$order_item_id = $input['order_item_id'] ?? null;

if (!$order_item_id) {
    echo json_encode(['success' => false, 'message' => 'Missing order_item_id']);
    exit;
}

try {
    // 1️⃣ Fetch item + order details
    $stmt = $pdo->prepare("
        SELECT oi.order_item_id, oi.price, oi.quantity, oi.status,
               o.order_id, o.payment_method, o.payment_status, o.payment_id, o.user_id
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        WHERE oi.order_item_id = ?
    ");
    $stmt->execute([$order_item_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        throw new Exception("Order item not found.");
    }

    if ($item['status'] === 'cancelled') {
        throw new Exception("This item has already been cancelled.");
    }

    $refund_amount = floatval($item['price']) * intval($item['quantity']);
    $payment_id = $item['payment_id'];

    // 2️⃣ Handle GCash refund via PayMongo
    if ($item['payment_method'] === 'gcash' && $item['payment_status'] === 'paid' && $payment_id) {

        $remaining_centavos = getRemainingRefundable($payment_id);
        $requested_centavos = intval(round($refund_amount * 100));

        if ($remaining_centavos <= 0) {
            throw new Exception("No refundable balance remaining for this payment.");
        }

        if ($requested_centavos > $remaining_centavos) {
            $requested_centavos = $remaining_centavos;
            $refund_amount = $requested_centavos / 100.0;
        }

        $pdo->beginTransaction();
        $refund = createRefund($payment_id, $refund_amount, $item['order_id']);

        if (!is_array($refund) || empty($refund['data']['id'])) {
            $pdo->rollBack();
            throw new Exception("Invalid refund response from payment provider.");
        }

        $refund_id     = $refund['data']['id'];
        $refund_status = $refund['data']['attributes']['status'] ?? 'pending';

        // 3️⃣ Update item status
        $updateItem = $pdo->prepare("UPDATE order_items SET status = 'cancelled' WHERE order_item_id = ?");
        $updateItem->execute([$order_item_id]);

        // 4️⃣ Log refund
        $log = $pdo->prepare("INSERT INTO transactions (order_id, external_id, transaction_name, transaction_status, transaction_value)
                              VALUES (?, ?, 'refund', ?, ?)");
        $log->execute([$item['order_id'], $refund_id, $refund_status, $refund_amount]);

        // 5️⃣ Adjust order total
        $updateOrder = $pdo->prepare("
            UPDATE orders 
            SET total = total - :amt 
            WHERE order_id = :oid
        ");
        $updateOrder->execute([':amt' => $refund_amount, ':oid' => $item['order_id']]);

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Item refunded successfully.',
            'refund_id' => $refund_id,
            'refund_status' => $refund_status,
            'refunded_amount' => $refund_amount
        ]);
        exit;

    } else {
        // 6️⃣ Cash/unpaid fallback (no PayMongo refund)
        $pdo->beginTransaction();

        $pdo->prepare("UPDATE order_items SET status = 'cancelled' WHERE order_item_id = ?")
            ->execute([$order_item_id]);

        $pdo->prepare("UPDATE orders SET total = total - ? WHERE order_id = ?")
            ->execute([$refund_amount, $item['order_id']]);

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Item cancelled successfully (no online refund).',
            'refunded_amount' => $refund_amount
        ]);
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
