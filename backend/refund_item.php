<?php
// refund_item.php
require_once __DIR__ . '/db_script/db.php';
require_once __DIR__ . '/create_payment_intent.php';
session_start();

header('Content-Type: application/json');

$order_item_id = $_POST['order_item_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

$response = ['success' => false, 'message' => '', 'debug' => []];

if (!$order_item_id || !$user_id) {
    $response['message'] = 'Missing parameters';
    echo json_encode($response);
    exit;
}

try {
    // Fetch order item and belonging order/user
    $stmt = $pdo->prepare("
        SELECT oi.*, o.user_id, o.order_id, o.payment_method, o.payment_status, o.payment_id, o.total
        FROM order_items oi
        JOIN orders o ON o.order_id = oi.order_id
        WHERE oi.order_item_id = ?
    ");
    $stmt->execute([$order_item_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) throw new Exception("Order item not found.");

    if ($row['user_id'] != $user_id) throw new Exception("Not authorized.");

    if (!in_array($row['status'], ['pending', 'preparing', 'ready_for_delivery'])) {
        throw new Exception("Item cannot be refunded at this stage.");
    }

    $order_id = $row['order_id'];
    $refund_amount = floatval($row['price'] * $row['quantity']);

    $pdo->beginTransaction();

    // mark this item as cancelled/refunded locally
    $pdo->prepare("UPDATE order_items SET status = 'cancelled' WHERE order_item_id = ?")
        ->execute([$order_item_id]);

    // recalc order total
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(price * quantity),0) FROM order_items WHERE order_id = ? AND status != 'cancelled'");
    $stmt->execute([$order_id]);
    $new_total = floatval($stmt->fetchColumn());
    $pdo->prepare("UPDATE orders SET total = ? WHERE order_id = ?")->execute([$new_total, $order_id]);

    // Initiate refund for this item only if original order was paid via provider
    if ($row['payment_method'] === 'gcash' && $row['payment_status'] === 'paid') {
        $effective_payment_id = $row['payment_id'] ?? null;
        if (empty($effective_payment_id)) {
            $tx = $pdo->prepare("SELECT external_id FROM transactions WHERE order_id = ? AND transaction_name='payment' AND transaction_status='success' ORDER BY transaction_id DESC LIMIT 1");
            $tx->execute([$order_id]);
            $t = $tx->fetch(PDO::FETCH_ASSOC);
            if ($t) $effective_payment_id = $t['external_id'] ?? null;
        }

        if (!empty($effective_payment_id)) {
            $remaining = getRemainingRefundable($effective_payment_id);
            $requested_centavos = intval(round($refund_amount * 100));
            if ($requested_centavos > $remaining) $requested_centavos = $remaining;
            if ($requested_centavos > 0) {
                $refund_pesos = $requested_centavos / 100.0;
                $refund_response = createRefund($effective_payment_id, $refund_pesos, $order_id);
                // Do NOT insert transactions here — webhook will log it
                // Optionally mark refund_requested_at
                $pdo->prepare("UPDATE orders SET refund_requested_at = NOW() WHERE order_id = ?")->execute([$order_id]);
                $response['refund_initiated'] = true;
                $response['refund_info'] = [
                    'id' => $refund_response['data']['id'] ?? null,
                    'status' => $refund_response['data']['attributes']['status'] ?? 'pending',
                    'amount' => $refund_pesos
                ];
            }
        }
    }

    $pdo->commit();

    $response['success'] = true;
    $response['message'] = 'Item cancelled and refund initiated (if applicable)';
    $response['new_total'] = $new_total;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $response['success'] = false;
    $response['message'] = 'Failed: ' . $e->getMessage();
    $response['debug'][] = $e->getTraceAsString();
}

echo json_encode($response);
