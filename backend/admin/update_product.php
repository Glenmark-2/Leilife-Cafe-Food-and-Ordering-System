<?php
require_once __DIR__ . '/../../backend/db_script/db.php';
require_once __DIR__ . '/../../backend/db_script/env.php';

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents("php://input"), true);

    $product_id   = intval($input['product_id'] ?? 0);
    $product_name = trim($input['product_name'] ?? '');
    $product_price = floatval($input['product_price'] ?? 0);
    $main_category_name = trim($input['main_category_name'] ?? '');
    $status = trim($input['status'] ?? '');

    if (!$product_id || !$product_name || !$product_price || !$main_category_name) {
        echo json_encode(["success" => false, "message" => "Invalid input"]);
        exit;
    }

    // Step 1: Get main_category_id from main_category_name
    $stmt = $pdo->prepare("SELECT main_category_id FROM categories WHERE main_category_name = :main LIMIT 1");
    $stmt->execute([':main' => $main_category_name]);
    $main = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$main) {
        echo json_encode(["success" => false, "message" => "Main category not found"]);
        exit;
    }

    $main_category_id = $main['main_category_id'];

    // Step 2: Pick one category_id under that main_category_id
    $stmt = $pdo->prepare("SELECT category_id FROM categories WHERE main_category_id = :main LIMIT 1");
    $stmt->execute([':main' => $main_category_id]);
    $cat = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cat) {
        echo json_encode(["success" => false, "message" => "No subcategory found for this main category"]);
        exit;
    }

    $category_id = $cat['category_id'];

    // Step 3: Update product
    $update = $pdo->prepare("
        UPDATE products 
        SET product_name = :name,
            product_price = :price,
            category_id = :category_id,
            status = :status
        WHERE product_id = :id
    ");

    $update->execute([
        ':name' => $product_name,
        ':price' => $product_price,
        ':category_id' => $category_id,
        ':status' => $status,
        ':id' => $product_id
    ]);

    // Step 4: Return updated product with join
    $stmt = $pdo->prepare("
        SELECT p.product_id, p.product_name, p.product_price, p.status, 
               c.category_id, c.main_category_id, mc.main_category_name
        FROM products p
        JOIN categories c ON p.category_id = c.category_id
        JOIN categories mc ON c.main_category_id = mc.main_category_id
        WHERE p.product_id = :id
    ");
    $stmt->execute([':id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "product" => $product]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
