<?php
require_once __DIR__ . '/../db_script/db.php';

$response = ["success" => false, "message" => ""];

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $productName  = $_POST["product_name"] ?? null;
        $productPrice = $_POST["product_price"] ?? null;
        $categoryId   = $_POST["category_id"] ?? null;
        $status       = $_POST["status"] ?? "Available";

        // ✅ Validation
        if (!$productName || !$productPrice || !$categoryId) {
            throw new Exception("All fields are required.");
        }

        // ✅ Handle file upload
        $productPicture = null;
        if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . "/../../public/uploads/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileTmpPath = $_FILES["photo"]["tmp_name"];
            $fileName = uniqid() . "_" . basename($_FILES["photo"]["name"]);
            $destPath = $uploadDir . $fileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $productPicture = "public/uploads/" . $fileName;
            } else {
                throw new Exception("File upload failed.");
            }
        }

        // ✅ Insert to DB
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
    } else {
        throw new Exception("Invalid request method.");
    }
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

header("Content-Type: application/json");
echo json_encode($response);
