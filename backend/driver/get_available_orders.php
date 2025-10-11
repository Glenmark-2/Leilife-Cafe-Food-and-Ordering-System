<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../db_script/db.php';

try {
    // Orders ready for delivery and not yet claimed
    $sql = "
        SELECT o.order_id, o.order_number, o.total, o.status,
               u.first_name, u.last_name, u.phone_number,
               a.street_address, a.barangay, a.city, a.latitude, a.longitude
        FROM orders o
        JOIN users u ON o.user_id = u.user_id
        LEFT JOIN addresses a ON o.user_id = a.user_id
        LEFT JOIN driver_orders d ON o.order_id = d.order_id
        WHERE o.status = 'ready_for_delivery'
          AND d.order_id IS NULL
        ORDER BY o.order_date DESC
    ";
    $stmt = $pdo->query($sql);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch order items per order
    $itemsStmt = $pdo->prepare("
        SELECT oi.quantity, p.product_name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?
    ");

    foreach ($orders as &$order) {
        $itemsStmt->execute([$order['order_id']]);
        $order['items'] = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($order);

    echo json_encode(['success' => true, 'orders' => $orders]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
