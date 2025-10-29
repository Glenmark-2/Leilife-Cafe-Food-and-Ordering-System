<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db_script/db.php';
require_once __DIR__ . '/../create_payment_intent.php'; // must provide createRefund() and getRemainingRefundable()

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "error" => "Invalid request"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$order_id = $data['order_id'] ?? null;
$new_status = $data['status'] ?? null;

$valid_status = ['pending', 'preparing', 'ready_for_delivery', 'delivered', 'cancelled', 'picked_up'];

if (!$order_id || !in_array($new_status, $valid_status)) {
    echo json_encode(["success" => false, "error" => "Invalid input"]);
    exit;
}

try {
    $pdo->beginTransaction();

    // Fetch order
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("Order not found");
    }

    // Keep important values for refunding
    $orig_order_total = floatval($order['total'] ?? 0.0);
    $payment_method = $order['payment_method'] ?? null;
    $payment_status = $order['payment_status'] ?? null;
    $payment_id = $order['payment_id'] ?? null;

    // === 1) Update order status ===
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt->execute([$new_status, $order_id]);

    $refund_result = null;

    // === 2) Determine item status mapping ===
    $item_status_map = [
        'pending' => 'pending',
        'preparing' => 'preparing',
        'ready_for_delivery' => 'finished',
        'delivered' => 'finished',
        'picked_up' => 'finished',
        'cancelled' => 'cancelled'
    ];

    $item_status = $item_status_map[$new_status] ?? 'pending';

    // === 3) Sync all order_items with corresponding status ===
    $stmt = $pdo->prepare("UPDATE order_items SET status = ? WHERE order_id = ?");
    $stmt->execute([$item_status, $order_id]);

    // === 4) Handle special case: Cancelled ===
    if ($new_status === 'cancelled') {
        // Recalculate total excluding cancelled items
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(price * quantity), 0) AS subtotal
            FROM order_items
            WHERE order_id = ? AND status != 'cancelled'
        ");
        $stmt->execute([$order_id]);
        $subtotal = floatval($stmt->fetchColumn());
        $new_total = $subtotal;

        $stmt = $pdo->prepare("UPDATE orders SET total = ? WHERE order_id = ?");
        $stmt->execute([$new_total, $order_id]);

        // === 5) Refund logic (if applicable) ===
        if ($payment_method === 'gcash' && $payment_status === 'paid') {
            $effective_payment_id = $payment_id;

            // Try fallback to transactions if no payment_id
            if (empty($effective_payment_id)) {
                $tx = $pdo->prepare("SELECT external_id, transaction_value 
                                     FROM transactions 
                                     WHERE order_id = ? 
                                       AND transaction_name = 'payment' 
                                       AND transaction_status = 'success' 
                                     ORDER BY transaction_id DESC LIMIT 1");
                $tx->execute([$order_id]);
                $transaction = $tx->fetch(PDO::FETCH_ASSOC);
                if ($transaction && !empty($transaction['external_id'])) {
                    $effective_payment_id = $transaction['external_id'];
                }
            }

            if (empty($effective_payment_id)) {
                error_log("Refund: no payment_id found for order {$order_id}. Skipping provider refund.");
                $refund_result = [
                    'success' => false,
                    'message' => 'No payment_id available to perform provider refund; items cancelled locally.'
                ];
            } else {
                // Try getting remaining refundable balance
                try {
                    $remaining_centavos = getRemainingRefundable($effective_payment_id);
                } catch (Exception $e) {
                    error_log("Refund: failed to fetch remaining refundable for payment {$effective_payment_id}: " . $e->getMessage());
                    $remaining_centavos = 0;
                }

                if ($remaining_centavos <= 0) {
                    $refund_result = [
                        'success' => false,
                        'message' => 'No refundable balance remaining at payment provider.'
                    ];
                } else {
                    // Compute refund amount (capped to remaining)
                    $requested_centavos = intval(round($orig_order_total * 100));
                    if ($requested_centavos > $remaining_centavos) {
                        $requested_centavos = $remaining_centavos;
                    }

                    $refund_pesos = $requested_centavos / 100.0;

                    try {
                        $refund_response = createRefund($effective_payment_id, $refund_pesos, $order_id);

                        if (!is_array($refund_response) || empty($refund_response['data']['id'])) {
                            throw new Exception("Invalid refund response from provider");
                        }

                        $provider_refund_id = $refund_response['data']['id'];
                        $provider_refund_status = $refund_response['data']['attributes']['status'] ?? 'pending';

                        // Log refund in transactions table
                        $ins = $pdo->prepare("INSERT INTO transactions 
                                              (order_id, external_id, transaction_name, transaction_status, transaction_value)
                                              VALUES (?, ?, 'refund', ?, ?)");
                        $ins->execute([$order_id, $provider_refund_id, $provider_refund_status, $refund_pesos]);

                        // Update payment status accordingly
                        $remaining_centavos_after = $remaining_centavos - $requested_centavos;
                        $new_payment_status = ($remaining_centavos_after <= 0) ? 'refunded' : 'partially_refunded';

                        $upd = $pdo->prepare("UPDATE orders SET payment_status = ? WHERE order_id = ?");
                        $upd->execute([$new_payment_status, $order_id]);

                        $refund_result = [
                            'success' => true,
                            'refund_id' => $provider_refund_id,
                            'refund_status' => $provider_refund_status,
                            'refunded_amount' => $refund_pesos,
                            'remaining_centavos_after' => $remaining_centavos_after
                        ];
                    } catch (Exception $e) {
                        error_log("Refund: createRefund failed for payment {$effective_payment_id}: " . $e->getMessage());
                        $refund_result = [
                            'success' => false,
                            'message' => 'Payment provider refund failed: ' . $e->getMessage()
                        ];
                    }
                }
            }
        } // end gcash refund
    } // end cancelled block

    // === 6) Commit transaction ===
    $pdo->commit();

    // === 7) Return response ===
    $response = [
        "success" => true,
        "order_id" => $order_id,
        "status" => $new_status,
        "item_status" => $item_status,
        "new_total" => isset($new_total) ? $new_total : null,
        "refund" => $refund_result
    ];

    echo json_encode($response);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
    exit;
}
