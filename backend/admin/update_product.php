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

    $productPrice = isset($_POST['product_price']) && $_POST['product_price'] !== ''
        ? trim($_POST['product_price'])
        : null;

    $priceLarge = isset($_POST['price_large']) && $_POST['price_large'] !== ''
        ? trim($_POST['price_large'])
        : null;

    $categoryId = intval($_POST['category_id'] ?? 0);
    $status     = trim($_POST['status'] ?? 'Available');
    $status     = strtolower($status) === 'unavailable' ? 'Unavailable' : 'Available';

    // Validation
    if ($productId <= 0 || $productName === '' || $categoryId <= 0) {
        throw new Exception("Missing required fields");
    }

    if ($productPrice === null && $priceLarge === null) {
        throw new Exception("Please provide at least a Medium or Large price.");
    }

    // Validate category
    $chk = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE category_id = ?");
    $chk->execute([$categoryId]);
    if ($chk->fetchColumn() == 0) {
        throw new Exception("Category not found (ID $categoryId)");
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

        $ext      = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid("prod_", true) . "." . strtolower($ext);
        $targetFile = $uploadDir . $fileName;

        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
            throw new Exception("Image upload failed");
        }
    }

    // Build UPDATE query dynamically
    $fields = "product_name = ?, product_price = ?, price_large = ?, category_id = ?, status = ?";
    $params = [
        $productName,
        $productPrice,
        $priceLarge,
        $categoryId,
        $status
    ];

    if ($fileName) {
        $fields .= ", product_picture = ?";
        $params[] = $fileName;
    }

    $params[] = $productId;

    $stmt = $pdo->prepare("UPDATE products SET $fields WHERE product_id = ?");

    // Bind values with NULL support
    $stmt->bindValue(1, $productName);
    $stmt->bindValue(2, $productPrice, $productPrice === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(3, $priceLarge, $priceLarge === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(4, $categoryId, PDO::PARAM_INT);
    $stmt->bindValue(5, $status);
    if ($fileName) $stmt->bindValue(6, $fileName);
    $stmt->bindValue($fileName ? 7 : 6, $productId, PDO::PARAM_INT);

    $stmt->execute();

    // Fetch updated product
    $stmt = $pdo->prepare("
        SELECT p.product_id, p.product_name, p.product_price, p.price_large, p.status, p.product_picture,
               c.category_name
        FROM products p
        JOIN categories c ON p.category_id = c.category_id
        WHERE p.product_id = ?
        LIMIT 1
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

// test