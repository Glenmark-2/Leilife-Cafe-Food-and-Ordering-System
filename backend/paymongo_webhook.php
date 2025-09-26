<?php
// paymongo_webhook.php
require_once __DIR__ . '/db_script/db.php';

header("Content-Type: application/json");

$rawPayload = file_get_contents("php://input");
$event = json_decode($rawPayload, true);

// Log payload for debugging
file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " " . $rawPayload . "\n", FILE_APPEND);

// (Optional) verify webhook signature
$signature = $_SERVER['HTTP_PAYMONGO_SIGNATURE'] ?? null;
// TODO: validate $signature against your webhook secret

if (!$event || !isset($event['data']['attributes'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid webhook payload"]);
    exit;
}

try {
    $type = $event['data']['attributes']['type'] ?? '';
    $attributes = $event['data']['attributes']['data']['attributes'] ?? [];

    $orderId = $attributes['metadata']['order_id'] ?? null;

    if ($orderId) {
        if ($type === "payment.paid") {
            // ✅ Update order status
            $stmt = $pdo->prepare("UPDATE orders 
                                   SET payment_status = 'paid', status = 'preparing' 
                                   WHERE order_id = :oid");
            $stmt->execute([':oid' => $orderId]);

            // ✅ Clear user's cart
            $clearCartStmt = $pdo->prepare("
                DELETE FROM cart_items 
                WHERE cart_id = (
                    SELECT cart_id FROM carts WHERE user_id = (
                        SELECT user_id FROM orders WHERE order_id = :oid
                    )
                )
            ");
            $clearCartStmt->execute([':oid' => $orderId]);

        } elseif ($type === "payment.failed") {
            // ❌ Mark order as failed
            $stmt = $pdo->prepare("UPDATE orders 
                                   SET payment_status = 'failed' 
                                   WHERE order_id = :oid");
            $stmt->execute([':oid' => $orderId]);
        }
    }

    echo json_encode(["success" => true]);
} catch (Exception $e) {
    file_put_contents(__DIR__ . "/paymongo_webhook_error.log", $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}