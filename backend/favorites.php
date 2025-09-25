<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

session_start();
require_once __DIR__ . '/db_script/db.php';

$isFavorite = false;

try {
    $userId = $_POST['user_id'] ?? null;
    $productId = $_POST['product_id'] ?? null;

    if (!$userId || !$productId) {
        echo json_encode(['success' => false, 'error' => 'Missing user_id or product_id']);
        exit;
    }

    $stmtCheck = $pdo->prepare("SELECT favorite_id FROM favorites WHERE user_id = :user AND product_id = :product LIMIT 1");
    $stmtCheck->execute(['user' => $userId, 'product' => $productId]);
    $favorite = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($favorite) {
        $stmtDelete = $pdo->prepare("DELETE FROM favorites WHERE favorite_id = :id");
        $stmtDelete->execute(['id' => $favorite['favorite_id']]);
        echo json_encode(['success' => true, 'action' => 'removed']);
        $isFavorite = false; 
    } else {
        $stmtInsert = $pdo->prepare("INSERT INTO favorites (user_id, product_id) VALUES (:user, :product)");
        $stmtInsert->execute(['user' => $userId, 'product' => $productId]);
        echo json_encode(['success' => true, 'action' => 'added']);
        $isFavorite = true; // update state
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Unexpected error: ' . $e->getMessage()]);
}
?>
