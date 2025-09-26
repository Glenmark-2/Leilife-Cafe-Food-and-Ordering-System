<?php

declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db_script/db.php';
require_once "../send_mail.php";

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    $identifier = trim($_POST['identifier'] ?? '');
    if (!$identifier) {
        echo json_encode(['success' => false, 'message' => 'Please enter email or username']);
        exit;
    }

    $user = null;

    // 1. Check admin_accounts table
    $stmt = $pdo->prepare("SELECT admin_id AS id, email, 'admin' AS role FROM admin_accounts WHERE email = :id OR username = :id LIMIT 1");
    $stmt->execute([':id' => $identifier]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Check driver_accounts table
    if (!$user) {
        $stmt = $pdo->prepare("SELECT driver_id AS id, email, 'driver' AS role FROM driver_accounts WHERE email = :id OR username = :id LIMIT 1");
        $stmt->execute([':id' => $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Email or username not found']);
        exit;
    }

    // ✅ Generate token & expiration BEFORE inserting
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

    $ins = $pdo->prepare("
        INSERT INTO password_resets (user_id, user_type, token, expires_at) 
        VALUES (:id, :type, :token, :expires_at)
    ");

    $ins->execute([
        ':id' => $user['id'],
        ':type' => $user['role'], // admin or driver
        ':token' => $token,
        ':expires_at' => $expiresAt
    ]);
    $link = "http://localhost/leilife/pages/admin/login-x9P2kL7zQ.php";

    // Send reset link email
    if (sendResetLink($user['email'], $token, $link)) {
        echo json_encode(['success' => true, 'message' => 'Reset link sent to your email']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send email']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
