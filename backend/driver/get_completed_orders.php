<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db_script/db.php'; // this provides $pdo

try {
    // Fetch ready-for-delivery orders with user + address info
    $sql = "
        SELECT 
            o.order_id,
            o.user_id,
            o.order_date,
            o.total,
            o.payment_method,
            o.payment_status,
            o.status,
            a.street_address,
            a.barangay,
            a.city,
            a.region,
            a.province,
            a.note_to_rider,
            a.pickup_location,
            a.latitude,
            a.longitude,
            u.first_name,
            u.last_name,
            u.phone_number
        FROM orders o
        LEFT JOIN addresses a ON o.user_id = a.user_id
        JOIN users u ON o.user_id = u.user_id
        WHERE o.status = 'ready_for_delivery'
        ORDER BY o.order_date DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $ordersData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $orders = [];
    foreach ($ordersData as $row) {
        $orderId = $row['order_id'];

        // Fetch order items + product name
        $itemSql = "
            SELECT 
                oi.order_item_id,
                oi.quantity,
                p.product_name
            FROM order_items oi
            JOIN products p ON oi.product_id = p.product_id
            WHERE oi.order_id = :order_id
        ";
        $itemStmt = $pdo->prepare($itemSql);
        $itemStmt->execute(['order_id' => $orderId]);
        $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

        $row['status'] = strtolower($row['status']); // normalize
        $row['items'] = $items;
        $orders[] = $row;
    }

    echo json_encode([
        "success" => true,
        "orders" => $orders
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
