<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

if (!isset($_SESSION['driver_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$driver_id = $_SESSION['driver_id'];
require_once __DIR__ . '/../db_script/db.php';

try {
    $sql = "
        SELECT o.order_id, o.order_number, o.total, o.status,
               u.first_name, u.last_name, u.phone_number,
               a.street_address, a.barangay, a.city, a.latitude, a.longitude,
               d.status AS driver_status, d.claimed_at
        FROM driver_orders d
        JOIN orders o ON d.order_id = o.order_id
        JOIN users u ON o.user_id = u.user_id
        LEFT JOIN addresses a ON o.user_id = a.user_id
        WHERE d.driver_id = ?
        ORDER BY d.claimed_at DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$driver_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch items
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
