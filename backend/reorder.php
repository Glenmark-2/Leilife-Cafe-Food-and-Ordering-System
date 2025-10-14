<?php
require_once __DIR__ . '/db_script/appData.php';
require_once __DIR__ . '/db_script/db.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;
$session_id = session_id();
$order_id = $_POST['order_id'] ?? null;

if (!$user_id) {
    echo "You must be logged in.";
    exit;
}

if (!$order_id) {
    echo "Order ID missing.";
    exit;
}

$appData = new AppData($pdo);

try {
    $appData->reorder($order_id, $user_id, $session_id);
    header("Location: /Leilife/public/index.php?page=checkout-page");

    exit;
} catch (Exception $e) {
    echo "Failed to reorder: " . $e->getMessage();
}
?>
