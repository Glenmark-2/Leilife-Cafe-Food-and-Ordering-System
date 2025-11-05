<?php
// cancel_order.php
require_once __DIR__ . '/db_script/appData.php';
require_once __DIR__ . '/db_script/db.php';
require_once __DIR__ . '/create_payment_intent.php'; // createRefund(), getRemainingRefundable()
session_start();

header('Content-Type: application/json');

$order_number = $_POST['order_number'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

$response = [
    'success' => false,
    'message' => '',
    'debug' => []
];

if (!$order_number || !$user_id) {
    $response['message'] = 'Invalid request';
    $response['debug'][] = 'Missing order_number or user_id';
    echo json_encode($response);
    exit;
}

try {
    // Verify ownership and fetch order
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ?");
    $stmt->execute([$order_number, $user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("Order not found or not owned by user.");
    }

    $order_id = $order['order_id'];
    $current_status = $order['status'];

    // Only allow cancellation when order is in cancellable states
    $cancellable = ['pending', 'preparing'];
    if (!in_array($current_status, $cancellable)) {
        throw new Exception("Order cannot be cancelled at this stage.");
    }

    $pdo->beginTransaction();

    // 1) Update order status
    $upd = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE order_id = ?");
    $upd->execute([$order_id]);

    // 2) Update order items to cancelled
    $pdo->prepare("UPDATE order_items SET status = 'cancelled' WHERE order_id = ?")
        ->execute([$order_id]);

    // 3) Recalculate total excluding cancelled items
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(price * quantity), 0) AS subtotal
        FROM order_items
        WHERE order_id = ? AND status != 'cancelled'
    ");
    $stmt->execute([$order_id]);
    $new_total = floatval($stmt->fetchColumn());

    $pdo->prepare("UPDATE orders SET total = ? WHERE order_id = ?")
        ->execute([$new_total, $order_id]);

    // 4) Refund initiation (if applicable) — DO NOT INSERT TRANSACTIONS HERE.
    // Let the PayMongo webhook be the single source of truth for transactions/refunds.
    $refund_result = null;
    $payment_method = $order['payment_method'] ?? null;
    $payment_status = $order['payment_status'] ?? null;
    $payment_id = $order['payment_id'] ?? null;
    $orig_total = floatval($order['total'] ?? 0.0);

    if ($payment_method === 'gcash' && $payment_status === 'paid') {
        // determine effective payment id (fallback to transactions if needed)
        $effective_payment_id = $payment_id;
        if (empty($effective_payment_id)) {
            $tx = $pdo->prepare("SELECT external_id FROM transactions WHERE order_id = ? AND transaction_name = 'payment' AND transaction_status = 'success' ORDER BY transaction_id DESC LIMIT 1");
            $tx->execute([$order_id]);
            $t = $tx->fetch(PDO::FETCH_ASSOC);
            if ($t && !empty($t['external_id'])) $effective_payment_id = $t['external_id'];
        }

        if (!empty($effective_payment_id)) {
            try {
                $remaining = getRemainingRefundable($effective_payment_id);
            } catch (Exception $e) {
                error_log("getRemainingRefundable failed for {$effective_payment_id}: " . $e->getMessage());
                $remaining = 0;
            }

            if ($remaining > 0) {
                $requested_centavos = intval(round($orig_total * 100));
                if ($requested_centavos > $remaining) $requested_centavos = $remaining;
                $refund_pesos = $requested_centavos / 100.0;

                try {
                    // Initiate the refund at the provider. DO NOT log provider refund in transactions here.
                    $refund_response = createRefund($effective_payment_id, $refund_pesos, $order_id);

                    if (!is_array($refund_response) || empty($refund_response['data']['id'])) {
                        throw new Exception("Invalid refund response from provider.");
                    }

                    $provider_refund_id = $refund_response['data']['id'];
                    $provider_refund_status = $refund_response['data']['attributes']['status'] ?? 'pending';

                    // Optionally store minimal local flag so UI can show refund was requested.
                    // Keep this lightweight and NOT duplicative of the webhook's authoritative records.
                    $pdo->prepare("UPDATE orders SET refund_requested_at = NOW() WHERE order_id = ?")
                        ->execute([$order_id]);

                    $refund_result = [
                        'success' => true,
                        'refund_id' => $provider_refund_id,
                        'refund_status' => $provider_refund_status,
                        'refunded_amount' => $refund_pesos
                    ];

                } catch (Exception $e) {
                    error_log("createRefund failed for {$effective_payment_id}: " . $e->getMessage());
                    $refund_result = [
                        'success' => false,
                        'message' => 'Failed to initiate refund: ' . $e->getMessage()
                    ];
                }
            } else {
                $refund_result = [
                    'success' => false,
                    'message' => 'No refundable balance remaining at provider.'
                ];
            }
        } else {
            $refund_result = [
                'success' => false,
                'message' => 'No payment identifier available for this order.'
            ];
        }
    }

    $pdo->commit();

    $response['success'] = true;
    $response['message'] = 'Order cancelled';
    $response['order_number'] = $order_number;
    $response['order_id'] = $order_id;
    $response['new_total'] = $new_total;
    $response['refund'] = $refund_result;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $response['success'] = false;
    $response['message'] = 'Cancellation failed: ' . $e->getMessage();
    $response['debug'][] = $e->getTraceAsString();
}

echo json_encode($response);
