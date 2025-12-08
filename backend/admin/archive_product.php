<?php
session_start();
require_once __DIR__ . '/../db_script/db.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    if (!isset($_POST['product_id'], $_POST['is_archive'])) {
        throw new Exception("Missing parameters");
    }

    $stmt = $pdo->prepare("UPDATE products SET is_archive = :is_archive WHERE product_id = :id");
    $stmt->execute([
        'is_archive' => $_POST['is_archive'],
        'id' => $_POST['product_id']
    ]);

    $response['success'] = true;
    $response['message'] = "Product updated";
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
