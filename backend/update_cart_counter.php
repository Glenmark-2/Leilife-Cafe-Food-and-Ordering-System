<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db_script/db.php';
require_once __DIR__ . '/db_script/appData.php';

$appData = new AppData($pdo);

$guestToken = $_COOKIE['guest_token'] ?? null;
$userId = $_SESSION['user_id'] ?? null;

$count = $appData->cartCounter($userId, $guestToken);

echo json_encode([
    'success' => true,
    'count' => $count
]);
