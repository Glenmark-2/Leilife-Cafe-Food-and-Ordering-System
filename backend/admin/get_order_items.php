<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db_script/db.php';

$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    echo json_encode(["success" => false, "error" => "Missing order_id"]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            oi.order_item_id,
            oi.order_id,
            p.product_name,
            p.product_picture,
            oi.quantity,
            oi.price,
            oi.status
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "items" => $items]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
