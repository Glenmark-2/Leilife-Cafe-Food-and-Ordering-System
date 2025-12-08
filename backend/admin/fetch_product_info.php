<?php
require_once __DIR__ . '/../db_script/db.php';
header('Content-Type: application/json');

// Enable temporary error display
error_reporting(E_ALL);
ini_set('display_errors', 1);

$productId = $_GET['product_id'] ?? null;

if (!$productId) {
    echo json_encode(['error' => 'No product ID provided']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            c.main_category_name,
            c.category_name,
            p.product_id,
            p.product_name,
            p.product_price,
            p.price_large,
            p.status,
            p.product_picture,
            CASE 
                WHEN p.has_flavor = 1 THEN GROUP_CONCAT(DISTINCT pf.flavor_name SEPARATOR ', ')
                ELSE NULL
            END AS flavors,
            CASE 
                WHEN p.has_size = 1 THEN (
                    SELECT GROUP_CONCAT(DISTINCT ds.size_name SEPARATOR ', ')
                    FROM drink_size ds
                    WHERE ds.status = 1
                )
                ELSE NULL
            END AS sizes
        FROM categories c
        RIGHT JOIN products p ON p.category_id = c.category_id
        LEFT JOIN product_flavors pf ON p.flavor_set_id = pf.flavor_set_id
        WHERE p.product_id = :p_id
        GROUP BY 
            c.main_category_name,
            c.category_name,
            p.product_id,
            p.product_name,
            p.product_price,
            p.price_large,
            p.status,
            p.product_picture
    ");
    
    $stmt->execute([':p_id' => $productId]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$info) {
        echo json_encode(['error' => 'Product not found']);
        exit;
    }

    echo json_encode($info);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
