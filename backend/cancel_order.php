<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db_script/db.php';
require_once __DIR__ . '/create_payment_intent.php'; // must contain corrected createRefund() and getRemainingRefundable()

header('Content-Type: application/json');

// --- Method check ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Invalid request method"]);
    exit;
}

$order_id = $_POST['order_id'] ?? null;
$user_id  = $_SESSION['user_id'] ?? null;
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

if (!$order_id) {
    http_response_code(400);
    echo json_encode(["error" => "Missing order_id"]);
    exit;
}

// --- Fetch order ---
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    http_response_code(404);
    echo json_encode(["error" => "Order not found"]);
    exit;
}

// --- Auth check ---
if ($user_id && $order['user_id'] != $user_id && !$is_admin) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

// --- Status restriction (only pending cancellable by user) ---
if (!$is_admin && $order['status'] !== 'pending') {
    http_response_code(400);
    echo json_encode(["error" => "Cannot cancel after preparing stage."]);
    exit;
}

// Helper: respond with JSON error
function respond_error($msg, $httpCode = 500) {
    http_response_code($httpCode);
    echo json_encode(["error" => $msg]);
    exit;
}

// --- Handle refund for paid GCash orders ---
if ($order['payment_method'] === 'gcash' && $order['payment_status'] === 'paid') {
    try {
        // Determine payment_id and refund_amount (preserve cents)
        $payment_id = $order['payment_id'] ?? null;
        $refund_amount = 0.0; // in PESOS (e.g. 185.50)

        if (!$payment_id) {
            // fallback: get transaction
            $tx = $pdo->prepare("SELECT external_id, transaction_value 
                                 FROM transactions 
                                 WHERE order_id = ? 
                                   AND transaction_name = 'payment' 
                                   AND transaction_status = 'success' 
                                 ORDER BY transaction_id DESC LIMIT 1");
            $tx->execute([$order_id]);
            $transaction = $tx->fetch(PDO::FETCH_ASSOC);

            if ($transaction) {
                if (!empty($transaction['external_id']) && str_starts_with($transaction['external_id'], 'pay_')) {
                    $payment_id = $transaction['external_id'];
                }
                // NOTE: assume transaction_value is PESOS (e.g. 185.50).
                // If your DB stores centavos instead, see debug below — code will handle it by capping to remaining.
                $refund_amount = floatval($transaction['transaction_value'] ?? 0.0);
            }
        } else {
            // Use `total` field from orders table (preserve cents)
            $refund_amount = floatval($order['total'] ?? 0.0);
        }

        if (!$payment_id) {
            respond_error("No valid payment found for refund.", 500);
        }

        if ($refund_amount <= 0) {
            respond_error("Refund amount must be greater than zero.", 400);
        }

        // Fetch remaining refundable from PayMongo (in CENTAVOS)
        try {
            $remaining_centavos = getRemainingRefundable($payment_id); // integer centavos
        } catch (Exception $e) {
            // log and bail with a clear message
            error_log("REFUND DEBUG: failed to fetch remaining refundable for payment_id={$payment_id}: " . $e->getMessage());
            respond_error("Unable to fetch refundable balance. " . $e->getMessage(), 500);
        }

        // Convert requested refund amount (PESOS float) to centavos, rounding properly
        $requested_centavos = intval(round(floatval($refund_amount) * 100));

        // DEBUG: log values so you can inspect the cause of "above maximum"
        error_log("REFUND DEBUG: payment_id={$payment_id} | requested_pesos={$refund_amount} | requested_centavos={$requested_centavos} | remaining_centavos={$remaining_centavos}");

        // If requested is larger than remaining, cap it to remaining (so PayMongo won't reject)
        if ($requested_centavos > $remaining_centavos) {
            // If remaining is zero -> nothing to refund
            if ($remaining_centavos <= 0) {
                respond_error("No refundable amount remaining for this payment.", 400);
            }

            // Adjust refund to remaining
            $requested_centavos = $remaining_centavos;
            $refund_amount = $requested_centavos / 100.0; // convert back to pesos for logging and DB
            error_log("REFUND DEBUG: capped refund to remaining. new_requested_pesos={$refund_amount} new_requested_centavos={$requested_centavos}");
        }

        // Now we have a safe centavo amount to refund. Call createRefund with PESOS value
        // (createRefund will convert PESOS to centavos internally)
        $pdo->beginTransaction();

        // call createRefund with pesos (constructed from centavos to avoid float rounding mismatch)
        $call_pesos = $requested_centavos / 100.0;
        error_log("REFUND DEBUG: calling createRefund(payment_id={$payment_id}, amount_pesos={$call_pesos}, order_id={$order_id})");
        $refund = createRefund($payment_id, $call_pesos, $order_id);

        // Validate refund structure
        if (!is_array($refund) || empty($refund['data']['id']) || empty($refund['data']['attributes'])) {
            $pdo->rollBack();
            respond_error("Invalid refund response from payment provider.", 502);
        }

        $refund_id     = $refund['data']['id'];
        $refund_attrs  = $refund['data']['attributes'];
        $refund_status = $refund_attrs['status'] ?? 'pending';

        // --- Update order (note: payment_status enum may not include 'refunded' in your schema)
        $updateOrder = $pdo->prepare("UPDATE orders 
                                      SET status = 'cancelled' 
                                      WHERE order_id = ?");
        $updateOrder->execute([$order_id]);

        // --- Cancel items ---
        $updateItems = $pdo->prepare("UPDATE order_items SET status = 'cancelled' WHERE order_id = ?");
        $updateItems->execute([$order_id]);

        // --- Log refund transaction ---
        $ins = $pdo->prepare("INSERT INTO transactions 
                              (order_id, external_id, transaction_name, transaction_status, transaction_value)
                              VALUES (?, ?, 'refund', ?, ?)");
        // store transaction_value in PESOS (consistent with earlier code)
        $ins->execute([$order_id, $refund_id, $refund_status, $refund_amount]);

        // commit DB changes
        $pdo->commit();

        echo json_encode([
            "success" => true,
            "message" => "Order refunded and cancelled successfully.",
            "refund_status" => $refund_status,
            "refund_id" => $refund_id,
            "requested_pesos" => number_format($refund_amount, 2),
            "requested_centavos" => $requested_centavos
        ]);
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        respond_error("Refund failed: " . $e->getMessage(), 500);
    }
}

// --- Default: cash or unpaid gcash (no payment provider refund) ---
try {
    $pdo->beginTransaction();
    $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE order_id = ?")->execute([$order_id]);
    $pdo->prepare("UPDATE order_items SET status = 'cancelled' WHERE order_id = ?")->execute([$order_id]);
    $pdo->commit();

    echo json_encode(["success" => true, "message" => "Order cancelled successfully."]);
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    respond_error("Failed to cancel order: " . $e->getMessage(), 500);
}
