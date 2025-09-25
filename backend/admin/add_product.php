<?php
require_once __DIR__ . '/../db_script/db.php';

header("Content-Type: application/json");

// Turn on errors for logging but not in output
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

$response = ["success" => false, "message" => ""];

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Invalid request method.");
    }

    // Raw inputs and trimming
    $rawName      = $_POST["product_name"] ?? null;
    $rawPrice     = $_POST["product_price"] ?? null;
    $rawCategory  = $_POST["category_id"] ?? null;
    $rawStatus    = $_POST["status"] ?? "Available";

    // Trim & normalize
    $productName  = $rawName ? ucwords(strtolower(trim($rawName))) : null; // Title Case + trimmed
    $productPrice = $rawPrice !== null ? trim($rawPrice) : null;
    $categoryId   = $rawCategory !== null ? trim($rawCategory) : null;
    $status       = $rawStatus !== null ? trim($rawStatus) : "Available";

    // Validation
    if (!$productName || !$productPrice || !$categoryId) {
        throw new Exception("All fields are required.");
    }

    // Allowed image types
    $allowedTypes = ["image/png", "image/jpeg", "image/jpg", "image/webp"];
    $productPicture = null;

    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] === UPLOAD_ERR_OK && !empty($_FILES["photo"]["name"])) {
        if (!in_array($_FILES["photo"]["type"], $allowedTypes)) {
            throw new Exception("Invalid file type. Allowed types: PNG, JPG, JPEG, WEBP.");
        }

        $uploadDir = __DIR__ . "/../../public/products/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $originalFileName = trim($_FILES["photo"]["name"]);                          
        $cleanFileName    = preg_replace("/[^A-Za-z0-9.\-_]/", "_", $originalFileName); 
        $fileName         = uniqid() . "_" . $cleanFileName;
        $destPath         = $uploadDir . $fileName;

        if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $destPath)) {
            throw new Exception("File upload failed.");
        }

        $productPicture = $fileName;
    }

    // Case-insensitive & trimmed duplicate name check
    $checkName = $pdo->prepare("SELECT COUNT(*) FROM products WHERE LOWER(TRIM(product_name)) = LOWER(TRIM(:name))");
    $checkName->execute([":name" => $productName]);
    if ($checkName->fetchColumn() > 0) {
        throw new Exception("A product with this name already exists.");
    }

    // Case-insensitive & trimmed duplicate picture check (only if picture uploaded)
    if ($productPicture) {
        $checkPic = $pdo->prepare("SELECT COUNT(*) FROM products WHERE LOWER(TRIM(product_picture)) = LOWER(TRIM(:pic))");
        $checkPic->execute([":pic" => trim($productPicture)]);
        if ($checkPic->fetchColumn() > 0) {
            throw new Exception("A product with this picture already exists.");
        }
    }

    // Insert using normalized (trimmed & title-cased) name
    $sql = "INSERT INTO products (product_name, product_price, category_id, status, product_picture) 
            VALUES (:name, :price, :category_id, :status, :picture)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":name"       => $productName,
        ":price"      => $productPrice,
        ":category_id"=> $categoryId,
        ":status"     => $status,
        ":picture"    => $productPicture
    ]);

    $response["success"] = true;
    $response["message"] = "Product added successfully.";
    $response["product_name"] = $productName; 
    $response["product_picture"] = $productPicture;
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
