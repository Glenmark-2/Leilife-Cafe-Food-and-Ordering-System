<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db_script/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "error" => "Invalid request"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$order_item_id = $data['order_item_id'] ?? null;
$new_status = $data['status'] ?? null;

$valid_status = ['pending', 'preparing', 'finished'];

if (!$order_item_id || !in_array($new_status, $valid_status)) {
    echo json_encode(["success" => false, "error" => "Invalid input"]);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE order_items SET status = ? WHERE order_item_id = ?");
    $stmt->execute([$new_status, $order_item_id]);

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
