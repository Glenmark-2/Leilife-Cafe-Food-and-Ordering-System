<?php
require_once __DIR__ . '/db_script/appData.php';
require_once __DIR__ . '/db_script/db.php';
session_start();
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
$session_id = session_id();
$order_id = $_POST['order_id'] ?? null;
$deleteCart = isset($_POST['delete_cart']) && $_POST['delete_cart'] == 1;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in.']);
    exit;
}

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Order ID missing.']);
    exit;
}

$appData = new AppData($pdo);

try {
    $result = $appData->reorder($order_id, $user_id, $session_id, 'delivery', $deleteCart);

    if (!$result['success']) {
        echo json_encode([
            'success' => false,
            'message' => $result['message'],
            'availableCount' => $result['availableCount'] ?? 0
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => $result['message'],
            'redirect' => '/Leilife/public/index.php?page=checkout-page'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to reorder: ' . $e->getMessage()
    ]);
}
