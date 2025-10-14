<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db_script/db.php';

try {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['order_id'])) {
        throw new Exception("Missing order_id");
    }

    $orderId = (int)$data['order_id'];

    // Fetch current order details
    $checkSql = "SELECT payment_method, payment_status FROM orders WHERE order_id = :order_id";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute(['order_id' => $orderId]);
    $order = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("Order not found");
    }

    // Build query depending on payment method
    if ($order['payment_method'] === 'cash' && $order['payment_status'] !== 'paid') {
        $sql = "UPDATE orders 
                SET status = 'delivered', payment_status = 'paid' 
                WHERE order_id = :order_id";
    } else {
        $sql = "UPDATE orders 
                SET status = 'delivered' 
                WHERE order_id = :order_id";
    }

    // ✅ Update orders table
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['order_id' => $orderId]);

    // ✅ ALSO update driver_orders table
    $driverSql = "UPDATE driver_orders SET status = 'completed' WHERE order_id = :order_id";
    $driverStmt = $pdo->prepare($driverSql);
    $driverStmt->execute(['order_id' => $orderId]);

    echo json_encode([
        "success" => true,
        "message" => "Order marked as delivered."
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
