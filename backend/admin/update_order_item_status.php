<?php
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 0); // hide errors from direct output
require_once __DIR__ . '/../db_script/db.php';
require_once __DIR__ . '/../create_payment_intent.php'; // for refund helpers

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "error" => "Invalid request"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$order_item_id = $data['order_item_id'] ?? null;
$new_status = $data['status'] ?? null;

$valid_status = ['pending', 'preparing', 'finished', 'cancelled'];

if (!$order_item_id || !in_array($new_status, $valid_status)) {
    echo json_encode(["success" => false, "error" => "Invalid input"]);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1️⃣ Fetch item + order info
    $stmt = $pdo->prepare("
        SELECT 
            oi.order_id,
            oi.price,
            oi.quantity,
            oi.status AS prev_status,
            o.payment_method,
            o.payment_status,
            o.payment_id,
            o.total
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        WHERE oi.order_item_id = ?
    ");
    $stmt->execute([$order_item_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        throw new Exception("Item not found.");
    }

    $order_id = $item['order_id'];
    $price = (float)$item['price'];
    $qty = (int)$item['quantity'];
    $prev_status = $item['prev_status'];
    $refund_amount = $price * $qty;

    // 2️⃣ Update item status
    $stmt = $pdo->prepare("UPDATE order_items SET status = ? WHERE order_item_id = ?");
    $stmt->execute([$new_status, $order_item_id]);

    // 3️⃣ Adjust order total if cancelled/reactivated
    if ($prev_status !== $new_status) {
        if ($new_status === 'cancelled') {
            $stmt = $pdo->prepare("UPDATE orders SET total = total - ? WHERE order_id = ?");
            $stmt->execute([$refund_amount, $order_id]);
        } elseif ($prev_status === 'cancelled') {
            $stmt = $pdo->prepare("UPDATE orders SET total = total + ? WHERE order_id = ?");
            $stmt->execute([$refund_amount, $order_id]);
        }
    }

// 4️⃣ Trigger PayMongo refund if GCash & paid
$refund_info = null;

if (
    $new_status === 'cancelled' &&
    $item['payment_method'] === 'gcash' &&
    $item['payment_status'] === 'paid' &&
    !empty($item['payment_id'])
) {
    $payment_id = $item['payment_id'];
    $remaining_centavos = getRemainingRefundable($payment_id);
    $requested_centavos = intval(round($refund_amount * 100));

    if ($remaining_centavos <= 0) {
        throw new Exception("No refundable balance remaining for this payment.");
    }

    if ($requested_centavos > $remaining_centavos) {
        $requested_centavos = $remaining_centavos;
        $refund_amount = $requested_centavos / 100.0;
    }

    try {
        // Actual PayMongo refund call
        $refund = createRefund($payment_id, $refund_amount, $order_id, $order_item_id, "requested_by_customer");

        // Normalize structure (PayMongo sometimes returns object or nested attributes)
        $refundData = null;

        if (isset($refund['data']['id'])) {
            $refundData = $refund['data'];
        } elseif (isset($refund['id'])) {
            $refundData = $refund;
        }

        if ($refundData && isset($refundData['id'])) {
            $refund_id = $refundData['id'];
            $refund_status = $refundData['attributes']['status'] ?? $refundData['status'] ?? 'pending';
            $refund_amount_cents = $refundData['attributes']['amount'] ?? $refundData['amount'] ?? $requested_centavos;

            $refund_info = [
                "id" => $refund_id,
                "status" => $refund_status,
                "amount" => $refund_amount_cents / 100
            ];

            // ✅ Update payment_status only (webhook will log the refund)
            $remaining_centavos_after = $remaining_centavos - $requested_centavos;
            $new_payment_status = ($remaining_centavos_after <= 0) ? 'refunded' : 'partially_refunded';

            $pdo->prepare("UPDATE orders SET payment_status = ? WHERE order_id = ?")
                ->execute([$new_payment_status, $order_id]);
        } else {
            $refund_info = null; // handle gracefully
        }

    } catch (Exception $refundEx) {
        error_log("Refund failed for order_item_id {$order_item_id}: " . $refundEx->getMessage());
        $refund_info = null;
    }
}


    // 5️⃣ Determine new overall order status
    $stmt = $pdo->prepare("SELECT status FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $statuses = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $active_statuses = array_filter($statuses, fn($s) => strtolower(trim($s)) !== 'cancelled');
    $new_order_status = 'pending';

    if (empty($active_statuses)) {
        $new_order_status = 'cancelled';
    } else {
        $unique = array_unique($active_statuses);
        $all_finished = count($unique) === 1 && $unique[0] === 'finished';
        $any_preparing = in_array('preparing', $active_statuses);
        $any_finished  = in_array('finished', $active_statuses);
        $any_pending   = in_array('pending', $active_statuses);

        if ($all_finished) {
            $new_order_status = 'ready_for_delivery';
        } elseif ($any_preparing) {
            $new_order_status = 'preparing';
        } elseif ($any_finished && $any_pending) {
            $new_order_status = 'preparing';
        } elseif (!$any_preparing && !$any_finished && $any_pending) {
            $new_order_status = 'pending';
        }
    }

    // 6️⃣ Update order status
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt->execute([$new_order_status, $order_id]);

    // 7️⃣ Fetch updated total
    $stmt = $pdo->prepare("SELECT total FROM orders WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $new_total = $stmt->fetchColumn();

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "order_id" => $order_id,
        "new_total" => $new_total,
        "order_status" => $new_order_status,
        "item_status" => $new_status,
        "refund" => $refund_info
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
