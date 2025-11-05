<?php
// update_order_status.php
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

    // Store important payment info
    $orig_order_total = floatval($order['total'] ?? 0.0);
    $payment_method = $order['payment_method'] ?? null;
    $payment_status = $order['payment_status'] ?? null;
    $payment_id = $order['payment_id'] ?? null;

    // === 1) Update order status ===
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt->execute([$new_status, $order_id]);

    $refund_result = null;

    // === 2) Update order_items to match ===
    $item_status_map = [
        'pending' => 'pending',
        'preparing' => 'preparing',
        'ready_for_delivery' => 'finished',
        'delivered' => 'finished',
        'picked_up' => 'finished',
        'cancelled' => 'cancelled'
    ];
    $item_status = $item_status_map[$new_status] ?? 'pending';
    $stmt = $pdo->prepare("UPDATE order_items SET status = ? WHERE order_id = ?");
    $stmt->execute([$item_status, $order_id]);

    // === 3) If cancelled, trigger refund if needed ===
    if ($new_status === 'cancelled' && $payment_method === 'gcash' && $payment_status === 'paid') {

        if (empty($payment_id)) {
            error_log("Refund skipped: no payment_id for order {$order_id}");
            $refund_result = ['success' => false, 'message' => 'No payment_id available'];
        } else {
            // Check remaining refundable amount (for safety)
            $remaining_centavos = 0;
            try {
                $remaining_centavos = getRemainingRefundable($payment_id);
            } catch (Exception $e) {
                error_log("getRemainingRefundable failed: " . $e->getMessage());
            }

            if ($remaining_centavos <= 0) {
                $refund_result = ['success' => false, 'message' => 'No refundable balance remaining'];
            } else {
                $requested_centavos = intval(round($orig_order_total * 100));
                if ($requested_centavos > $remaining_centavos) {
                    $requested_centavos = $remaining_centavos;
                }
                $refund_pesos = $requested_centavos / 100.0;

                try {
                    $refund_response = createRefund($payment_id, $refund_pesos, $order_id);

                    if (!is_array($refund_response) || empty($refund_response['data']['id'])) {
                        throw new Exception("Invalid refund response");
                    }

                    $refund_id = $refund_response['data']['id'];
                    $refund_status = $refund_response['data']['attributes']['status'] ?? 'pending';

                    // Mark local payment as pending_refund (temporary)
                    $upd = $pdo->prepare("UPDATE orders SET payment_status = ? WHERE order_id = ?");
                    $upd->execute(['pending_refund', $order_id]);

                    $refund_result = [
                        'success' => true,
                        'refund_id' => $refund_id,
                        'refund_status' => $refund_status,
                        'refunded_amount' => $refund_pesos
                    ];

                    // PayMongo webhook will later finalize transaction/refund records
                } catch (Exception $e) {
                    $refund_result = ['success' => false, 'message' => $e->getMessage()];
                    error_log("Refund creation failed: " . $e->getMessage());
                }
            }
        }
    }

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "order_id" => $order_id,
        "status" => $new_status,
        "item_status" => $item_status,
        "refund" => $refund_result
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
