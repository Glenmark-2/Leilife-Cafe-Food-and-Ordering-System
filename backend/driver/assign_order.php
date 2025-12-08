<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

if (!isset($_SESSION['driver_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$order_id = $input['order_id'] ?? 0;
$driver_id = $_SESSION['driver_id'];

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

require_once __DIR__ . '/../db_script/db.php';

try {
    $pdo->beginTransaction();

    // Check if already claimed
    $check = $pdo->prepare("SELECT order_id FROM driver_orders WHERE order_id = ? LIMIT 1");
    $check->execute([$order_id]);
    if ($check->fetch()) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Order already claimed']);
        exit;
    }

    // Double-check that the order is ready_for_delivery
    $checkOrder = $pdo->prepare("SELECT status FROM orders WHERE order_id = ?");
    $checkOrder->execute([$order_id]);
    $status = $checkOrder->fetchColumn();

    if ($status !== 'ready_for_delivery') {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Order is not ready for delivery']);
        exit;
    }

    // Claim order
    $insert = $pdo->prepare("
        INSERT INTO driver_orders (driver_id, order_id, status)
        VALUES (?, ?, 'claimed')
    ");
    $insert->execute([$driver_id, $order_id]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Order successfully claimed']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
