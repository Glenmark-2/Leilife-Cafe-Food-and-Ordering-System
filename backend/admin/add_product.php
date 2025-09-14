<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db_script/db.php';

$name     = $_POST['name']   ?? null;
$price    = $_POST['price']  ?? null;
$mainCat  = $_POST['category'] ?? null; // ito yung main_category_name (Meals/Drinks)
$status   = $_POST['status'] ?? "Available";

if (!$name || !$price || !$mainCat) {
    echo json_encode(["success" => false, "message" => "Missing fields"]);
    exit;
}

// Handle Image Upload
$imageName = "image-43.png"; // default
if (!empty($_FILES['photo']['name'])) {
    $uploadDir = __DIR__ . '/../../public/products/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $imageName = time() . "_" . preg_replace("/[^A-Za-z0-9.\-_]/", "_", $_FILES['photo']['name']);
    $targetFile = $uploadDir . $imageName;

    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
        echo json_encode(["success" => false, "message" => "Image upload failed"]);
        exit;
    }
}

try {
    // Hanapin ang category_id base sa main_category_name
    $stmt = $pdo->prepare("
        SELECT c.category_id 
        FROM categories c
        JOIN categories mc ON c.main_category_id = mc.main_category_id
        WHERE mc.main_category_name = ?
        LIMIT 1
    ");
    $stmt->execute([$mainCat]);
    $cat = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cat) {
        echo json_encode(["success" => false, "message" => "No category found for main category: $mainCat"]);
        exit;
    }

    $categoryId = $cat['category_id'];

    // Insert sa products gamit ang category_id
    $stmt = $pdo->prepare("
        INSERT INTO products (product_name, product_price, category_id, status, product_picture) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$name, $price, $categoryId, $status, $imageName]);

    echo json_encode([
        "success" => true,
        "product_image" => $imageName,
        "inserted_id" => $pdo->lastInsertId()
    ]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
