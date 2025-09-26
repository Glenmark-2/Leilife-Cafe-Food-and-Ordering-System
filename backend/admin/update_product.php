<?php
require_once __DIR__ . '/../db_script/db.php';

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    // Inputs
    $productId   = intval($_POST['product_id'] ?? 0);
    $productName = trim($_POST['product_name'] ?? '');
    $productPrice = $_POST['product_price'] ?? ''; 
    $priceLarge   = $_POST['price_large'] ?? '';  
    $categoryId   = intval($_POST['category_id'] ?? 0);
    $status       = trim($_POST['status'] ?? 'Available');
    $status       = strtolower($status) === 'unavailable' ? 'Unavailable' : 'Available';

    if ($productId <= 0 || $productName === '' || $categoryId <= 0) {
        throw new Exception("Missing required fields");
    }

    $stmt = $pdo->prepare("SELECT main_category_name FROM categories WHERE category_id = ?");
    $stmt->execute([$categoryId]);
    $categoryName = $stmt->fetchColumn();
    if (!$categoryName) throw new Exception("Category not found (ID $categoryId)");
    $categoryName = trim(strtolower($categoryName));

    $mediumPrice = $productPrice !== '' ? floatval($productPrice) : null;
    $largePrice  = $priceLarge !== '' ? floatval($priceLarge) : null;

    // Validation
    if ($categoryName === 'drinks') {
        if (($mediumPrice === null || $mediumPrice <= 0) && ($largePrice === null || $largePrice <= 0)) {
            throw new Exception("Please provide at least a Medium or Large price for drinks.");
        }

        // Validate entered prices individually
        if ($mediumPrice !== null && $mediumPrice <= 0) {
            throw new Exception("Medium price must be greater than 0.");
        }
        if ($largePrice !== null && $largePrice <= 0) {
            throw new Exception("Large price must be greater than 0.");
        }

    } else {
        // Non-drinks: medium required
        if ($mediumPrice === null || $mediumPrice <= 0) {
            throw new Exception("Product price is required and must be greater than 0 for this category.");
        }

        // Large price if entered
        if ($largePrice !== null && $largePrice <= 0) {
            throw new Exception("Large price must be greater than 0.");
        }
    }

    // Handle image upload
    $fileName = null;
    if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../public/products/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        // Delete old image if exists
        $stmt = $pdo->prepare("SELECT product_picture FROM products WHERE product_id = ?");
        $stmt->execute([$productId]);
        $oldImage = $stmt->fetchColumn();
        if ($oldImage && $oldImage !== "default_product.png") {
            $oldFile = $uploadDir . $oldImage;
            if (file_exists($oldFile)) @unlink($oldFile);
        }

        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid("prod_", true) . "." . strtolower($ext);
        $targetFile = $uploadDir . $fileName;
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
            throw new Exception("Image upload failed");
        }
    }

    // Build UPDATE query
    $fields = "product_name = ?, product_price = ?, price_large = ?, category_id = ?, status = ?";
    $params = [
        $productName,
        $mediumPrice !== null ? $mediumPrice : null,
        $largePrice !== null ? $largePrice : null,
        $categoryId,
        $status
    ];

    if ($fileName) {
        $fields .= ", product_picture = ?";
        $params[] = $fileName;
    }

    $params[] = $productId;
    $stmt = $pdo->prepare("UPDATE products SET $fields WHERE product_id = ?");
    $stmt->execute($params);

    // Fetch updated product
    $stmt = $pdo->prepare("
        SELECT p.product_id, p.product_name, p.product_price, p.price_large, p.status, p.product_picture,
               c.category_name
        FROM products p
        JOIN categories c ON p.category_id = c.category_id
        WHERE p.product_id = ? LIMIT 1
    ");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "product" => $product]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Update failed: " . $e->getMessage()
    ]);
}
