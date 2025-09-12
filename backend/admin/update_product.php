<?php
require_once __DIR__ . '/../db_script/db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Invalid data"]);
    exit;
}

try {
    // 1. Update the product
    $stmt = $pdo->prepare("UPDATE products 
        SET product_name = ?, 
            product_price = ?, 
            category_id = ?, 
            status = ? 
        WHERE product_id = ?");
    $result = $stmt->execute([
        $data['product_name'],
        $data['product_price'],
        $data['category_id'],
        $data['status'],
        $data['product_id']
    ]);

    if (!$result) {
        echo json_encode(["success" => false, "message" => "Failed to update"]);
        exit;
    }

    // 2. Fetch updated row with JOIN para makuha uli ang category + main_category_name
    $stmt = $pdo->prepare("
        SELECT p.product_id, 
               p.product_name, 
               p.product_price, 
               p.status,
               p.category_id,
               c.category_name,
               c.main_category_name
        FROM products p
        JOIN categories c ON p.category_id = c.category_id
        WHERE p.product_id = ?
    ");
    $stmt->execute([$data['product_id']]);
    $updatedProduct = $stmt->fetch(PDO::FETCH_ASSOC);

    // 3. Return success + updated product data
    echo json_encode([
        "success" => true,
        "product" => $updatedProduct
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
