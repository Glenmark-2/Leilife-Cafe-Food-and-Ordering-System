<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db_script/db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) throw new Exception("Invalid request payload");

    $name = trim($data['name'] ?? '');
    $number = trim($data['number'] ?? '');
    $email = trim($data['email'] ?? '');
    $methods = $data['methods'] ?? [];

    if (!$name || !$number || !$email) throw new Exception("All fields are required");
    if (!preg_match('/^09\d{9}$/', $number)) throw new Exception("Invalid GCash number format");
    if (!is_array($methods) || count($methods) < 1) throw new Exception("At least one payment method must be enabled");

    $enabled = array_filter($methods, fn($m) => $m['status'] == 1);
    if (count($enabled) < 1) throw new Exception("At least one payment method must be enabled");

    $pdo->beginTransaction();

    // Update GCash info
    $stmt = $pdo->prepare("UPDATE payment_settings SET gcash_name = ?, gcash_number = ?, gcash_email = ? WHERE id = 1");
    $stmt->execute([$name, $number, $email]);

    // Update payment methods
    $stmt2 = $pdo->prepare("UPDATE payment_methods SET status = :status WHERE payment_id = :id");
    foreach ($methods as $m) {
        $stmt2->execute([
            ':status' => $m['status'] ? 'enabled' : 'disabled',
            ':id' => $m['id']
        ]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Payment settings updated successfully']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
