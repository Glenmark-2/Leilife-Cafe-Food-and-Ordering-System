<?php
require_once __DIR__ . '/db_script/appData.php';
require_once __DIR__ . '/db_script/db.php';
session_start();

header('Content-Type: application/json');

$order_number = $_POST['order_number'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

$response = [
    'success' => false,
    'message' => '',
    'debug' => []
];

// Validate input
if (!$order_number || !$user_id) {
    $response['message'] = 'Invalid request';
    $response['debug'][] = 'Missing order_number or user_id';
    echo json_encode($response);
    exit;
}

$response['debug'][] = "Received order_number: $order_number, user_id: $user_id";

// Initialize AppData
$appData = new AppData($pdo);

// Check if order exists and belongs to this user
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = :onum AND user_id = :uid");
$stmt->execute([':onum' => $order_number, ':uid' => $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    $response['message'] = 'Order not found';
    $response['debug'][] = "Query returned no rows";
    echo json_encode($response);
    exit;
}

$response['debug'][] = "Order found, current status: " . $order['status'];

// Attempt to cancel
try {
    $appData->cancelOrder($order_number);
    $response['success'] = true;
    $response['message'] = 'Order cancelled successfully';
    $response['debug'][] = "cancelOrder executed";
} catch (Exception $e) {
    $response['message'] = 'Failed to cancel order';
    $response['debug'][] = "Exception: " . $e->getMessage();
}

echo json_encode($response);
