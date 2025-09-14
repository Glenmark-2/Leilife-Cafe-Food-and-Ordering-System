<?php
require_once __DIR__ . '/../db_script/db.php';

// Set JSON header for response
header('Content-Type: application/json');

try {
    // Check kung may file upload (FormData)
    if (!isset($_POST['product_id'])) {
        echo json_encode(["success" => false, "message" => "Invalid request."]);
        exit;
    }

    $productId   = $_POST['product_id'];
    $productName = $_POST['product_name'] ?? '';
    $productPrice = $_POST['product_price'] ?? '';
    $mainCategoryName = $_POST['main_category_name'] ?? '';
   $status = $_POST['status'] ?? 'available';
$status = strtolower($status) === 'unavailable' ? 'Unavailable' : 'Available';

    // Hanapin ang category_id mula sa main_category_name
   $stmt = $pdo->prepare("
    SELECT c.category_id 
    FROM categories c
    JOIN categories mc ON c.main_category_id = mc.main_category_id
    WHERE mc.main_category_name = ?
    LIMIT 1
");

    $stmt->execute([$mainCategoryName]);
    $category = $stmt->fetch();

    if (!$category) {
        echo json_encode(["success" => false, "message" => "Category not found"]);
        exit;
    }

    $categoryId = $category['category_id'];

    // Handle image upload kung meron
    $fileName = null;
    if (!empty($_FILES['photo']['name'])) {
    $uploadDir = __DIR__ . '/../../public/products/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Kunin muna current image ng product
    $stmt = $pdo->prepare("SELECT product_picture FROM products WHERE product_id = ?");
    $stmt->execute([$productId]);
    $currentImage = $stmt->fetchColumn();

    // Kung may luma at hindi default, i-delete
    if ($currentImage && $currentImage !== "default_product.png") {
        $oldFile = $uploadDir . $currentImage;
        if (file_exists($oldFile)) {
            unlink($oldFile);
        }
    }

    // Save bagong image
    $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $fileName = uniqid("prod_", true) . "." . strtolower($ext);
    $targetFile = $uploadDir . $fileName;

    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
        echo json_encode(["success" => false, "message" => "Image upload failed"]);
        exit;
    }
}
    // Update query depende kung may bagong image
    if ($fileName) {
        $stmt = $pdo->prepare("
            UPDATE products 
            SET product_name = ?, product_price = ?, category_id = ?, status = ?, product_picture = ?
            WHERE product_id = ?
        ");
        $stmt->execute([$productName, $productPrice, $categoryId, $status, $fileName, $productId]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE products 
            SET product_name = ?, product_price = ?, category_id = ?, status = ?
            WHERE product_id = ?
        ");
        $stmt->execute([$productName, $productPrice, $categoryId, $status, $productId]);
    }

    // Kunin updated product + join categories
    $stmt = $pdo->prepare("
        SELECT p.product_id, p.product_name, p.product_price, p.status, p.product_picture,
               c.category_name, mc.main_category_name
        FROM products p
        JOIN categories c ON p.category_id = c.category_id
        JOIN categories mc ON c.main_category_id = mc.main_category_id
        WHERE p.product_id = ?
    ");
    $stmt->execute([$productId]);
    $updatedProduct = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "product" => $updatedProduct]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
