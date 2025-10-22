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

    // Raw inputs
    $rawName      = $_POST["product_name"] ?? null;
    $rawPrice     = $_POST["product_price"] ?? null;
    $rawPriceLarge= $_POST["price_large"] ?? null;
    $rawCategory  = $_POST["category"] ?? null; // can be id or new name
    $rawMainCategory = $_POST["main_category"] ?? null; // can be id or new name
    $rawStatus    = $_POST["status"] ?? "Available";
    $rawFlavors   = $_POST["flavors"] ?? null;

    // Trim & normalize
    $productName  = $rawName ? ucwords(strtolower(trim($rawName))) : null;
    $productPrice = $rawPrice !== null ? trim($rawPrice) : null;
    $priceLarge   = $rawPriceLarge !== null ? trim($rawPriceLarge) : null;
    $status       = $rawStatus !== null ? trim($rawStatus) : "Available";

    if (!$productName || !$productPrice || !$rawCategory || !$rawMainCategory) {
        throw new Exception("Name, price, main category, and category are required.");
    }

    // Handle new main category
    if (is_numeric($rawMainCategory)) {
        $mainCategoryId = (int)$rawMainCategory;
    } else {
        $stmt = $pdo->prepare("INSERT INTO categories (main_category_id, main_category_name, category_name) VALUES (:main_id, :main_name, :cat_name)");
        // Generate new main_category_id as MAX+1
        $newMainId = (int)$pdo->query("SELECT IFNULL(MAX(main_category_id),0)+1 FROM categories")->fetchColumn();
        $stmt->execute([
            ":main_id" => $newMainId,
            ":main_name" => trim($rawMainCategory),
            ":cat_name" => trim($rawCategory)
        ]);
        $mainCategoryId = $newMainId;
        $categoryId = $pdo->lastInsertId();
    }

    // Handle subcategory if new
    if (is_numeric($rawCategory)) {
        $categoryId = (int)$rawCategory;
    } elseif (!isset($categoryId)) {
        $stmt = $pdo->prepare("INSERT INTO categories (main_category_id, main_category_name, category_name) VALUES (:main_id, (SELECT main_category_name FROM categories WHERE main_category_id=:main_id LIMIT 1), :cat_name)");
        $stmt->execute([
            ":main_id" => $mainCategoryId,
            ":cat_name"=> trim($rawCategory)
        ]);
        $categoryId = $pdo->lastInsertId();
    }

    // Allowed image types
    $allowedTypes = ["image/png", "image/jpeg", "image/jpg", "image/webp"];
    $productPicture = null;
    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] === UPLOAD_ERR_OK && !empty($_FILES["photo"]["name"])) {
        if (!in_array($_FILES["photo"]["type"], $allowedTypes)) {
            throw new Exception("Invalid file type. Allowed types: PNG, JPG, JPEG, WEBP.");
        }
        $uploadDir = __DIR__ . "/../../public/products/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $fileName = uniqid() . "_" . preg_replace("/[^A-Za-z0-9.\-_]/", "_", trim($_FILES["photo"]["name"]));
        $destPath = $uploadDir . $fileName;
        if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $destPath)) {
            throw new Exception("File upload failed.");
        }
        $productPicture = $fileName;
    }

    // Duplicate name check
    $checkName = $pdo->prepare("SELECT COUNT(*) FROM products WHERE LOWER(TRIM(product_name)) = LOWER(TRIM(:name))");
    $checkName->execute([":name" => $productName]);
    if ($checkName->fetchColumn() > 0) throw new Exception("A product with this name already exists.");

    // Duplicate picture check
    if ($productPicture) {
        $checkPic = $pdo->prepare("SELECT COUNT(*) FROM products WHERE LOWER(TRIM(product_picture)) = LOWER(TRIM(:pic))");
        $checkPic->execute([":pic" => trim($productPicture)]);
        if ($checkPic->fetchColumn() > 0) throw new Exception("A product with this picture already exists.");
    }

    // Handle flavors
    $hasFlavor = 0;
    $flavorSetId = null;
    if ($rawFlavors) {
        $flavorsData = json_decode($rawFlavors, true);
        if (isset($flavorsData['existing_flavor_set_id'])) {
            $flavorSetId = $flavorsData['existing_flavor_set_id'];
            $hasFlavor = 1;
        } elseif (isset($flavorsData['new_flavors']) && is_array($flavorsData['new_flavors']) && count($flavorsData['new_flavors']) > 0) {
            $flavorSetId = (int)$pdo->query("SELECT IFNULL(MAX(flavor_set_id),0)+1 FROM product_flavors")->fetchColumn();
            $stmt = $pdo->prepare("INSERT INTO product_flavors (flavor_name, status, flavor_set_id) VALUES (:flavor_name, 'available', :flavor_set_id)");
            foreach ($flavorsData['new_flavors'] as $flavorName) {
                $stmt->execute([
                    ":flavor_name" => trim($flavorName),
                    ":flavor_set_id" => $flavorSetId
                ]);
            }
            $hasFlavor = 1;
        }
    }

    // Determine has_size
    $hasSize = $priceLarge && is_numeric($priceLarge) && $priceLarge > 0 ? 1 : 0;

    // Insert product
    $sql = "INSERT INTO products 
            (product_name, product_price, price_large, category_id, status, product_picture, has_flavor, flavor_set_id, has_size) 
            VALUES (:name, :price, :price_large, :category_id, :status, :picture, :has_flavor, :flavor_set_id, :has_size)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":name"         => $productName,
        ":price"        => $productPrice,
        ":price_large"  => $priceLarge,
        ":category_id"  => $categoryId,
        ":status"       => $status,
        ":picture"      => $productPicture,
        ":has_flavor"   => $hasFlavor,
        ":flavor_set_id"=> $flavorSetId,
        ":has_size"     => $hasSize
    ]);

    $response["success"] = true;
    $response["message"] = "Product added successfully.";
    $response["product_name"] = $productName;
    $response["product_picture"] = $productPicture;
    $response["category_id"] = $categoryId;
    $response["main_category_id"] = $mainCategoryId;
    $response["flavor_set_id"] = $flavorSetId;
    $response["has_size"] = $hasSize;

} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
