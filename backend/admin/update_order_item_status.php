<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db_script/db.php';
require_once __DIR__ . '/../create_payment_intent.php'; // For refund functions

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

    // 1️⃣ Get item + order info
    $stmt = $pdo->prepare("
        SELECT oi.order_id, oi.price, oi.quantity, oi.status AS prev_status,
               o.payment_method, o.payment_status, o.payment_id, o.total
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

    // 2️⃣ Update the item status
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

    // 4️⃣ Handle GCash refund if needed
    $refund_id = null;
    $refund_status = null;

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

        // Call PayMongo refund
        $refund = createRefund($payment_id, $refund_amount, $order_id);

        if (!is_array($refund) || empty($refund['data']['id'])) {
            throw new Exception("Refund API error: invalid response.");
        }

        $refund_id = $refund['data']['id'];
        $refund_status = $refund['data']['attributes']['status'] ?? 'pending';

        // Log refund
        $log = $pdo->prepare("
            INSERT INTO transactions (order_id, external_id, transaction_name, transaction_status, transaction_value)
            VALUES (?, ?, 'refund', ?, ?)
        ");
        $log->execute([$order_id, $refund_id, $refund_status, $refund_amount]);
    }

    // 5️⃣ Determine new overall order status (FIXED LOGIC)
    $stmt = $pdo->prepare("SELECT status FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $statuses = array_diff($stmt->fetchAll(PDO::FETCH_COLUMN), ['cancelled']); // ignore cancelled items

    $new_order_status = 'pending'; // default

    if ($statuses) {
        $unique = array_unique($statuses);
        $all_finished = count($unique) === 1 && $unique[0] === 'finished';
        $any_preparing = in_array('preparing', $statuses);
        $any_finished  = in_array('finished', $statuses);
        $any_pending   = in_array('pending', $statuses);

        if (empty($statuses)) {
            $new_order_status = 'cancelled';
        } elseif ($all_finished) {
            $new_order_status = 'ready_for_delivery';
        } elseif ($any_preparing) {
            $new_order_status = 'preparing';
        } elseif ($any_finished && $any_pending) {
            $new_order_status = 'preparing'; // mix of finished + pending = still preparing
        } elseif (!$any_preparing && !$any_finished && $any_pending) {
            $new_order_status = 'pending';
        }
    } else {
        // all items cancelled
        $new_order_status = 'cancelled';
    }

    // 6️⃣ Update order status
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt->execute([$new_order_status, $order_id]);

    // 7️⃣ Get updated total
    $stmt = $pdo->prepare("SELECT total FROM orders WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $new_total = $stmt->fetchColumn();

    $pdo->commit();

    // ✅ Immediate UI update data
    echo json_encode([
        "success" => true,
        "order_id" => $order_id,
        "new_total" => $new_total,
        "order_status" => $new_order_status,
        "item_status" => $new_status,
        "refund" => $refund_id ? [
            "id" => $refund_id,
            "status" => $refund_status,
            "amount" => $refund_amount
        ] : null
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
