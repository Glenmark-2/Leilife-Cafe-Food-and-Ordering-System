<?php
require_once __DIR__ . '/db_script/db.php';
require_once __DIR__ . '/db_script/appData.php';
session_start();
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
$session_id = session_id();

if (!$user_id) {
    echo json_encode(['hasItems' => false]);
    exit;
}

try {
    // Check if cart exists
    $stmtCart = $pdo->prepare("
        SELECT c.cart_id 
        FROM carts c
        WHERE c.user_id = :user_id OR c.session_id = :session_id
        LIMIT 1
    ");
    $stmtCart->execute([
        ':user_id' => $user_id,
        ':session_id' => $session_id
    ]);
    $cart = $stmtCart->fetch(PDO::FETCH_ASSOC);

    if (!$cart) {
        echo json_encode(['hasItems' => false]);
        exit;
    }

    // Check if cart has items
    $stmtItems = $pdo->prepare("
        SELECT COUNT(*) 
        FROM cart_items
        WHERE cart_id = :cart_id
    ");
    $stmtItems->execute([':cart_id' => $cart['cart_id']]);
    $count = (int) $stmtItems->fetchColumn();

    echo json_encode(['hasItems' => $count > 0]);
} catch (Exception $e) {
    echo json_encode(['hasItems' => false, 'error' => $e->getMessage()]);
}
