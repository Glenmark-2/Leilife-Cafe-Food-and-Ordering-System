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

    $token = trim($_POST['token'] ?? '');
    if (!$token) {
        echo json_encode(['success' => false, 'message' => 'Missing token']);
        exit;
    }

    // 1. Lookup old reset request WITHOUT checking expiration
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = :token LIMIT 1");
    $stmt->execute([':token' => $token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset) {
        echo json_encode(['success' => false, 'message' => 'Invalid token']);
        exit;
    }

    // 2. Get user info from respective table
    if ($reset['user_type'] === 'admin') {
        $stmt = $pdo->prepare("SELECT admin_id AS id, email, 'admin' AS role 
                               FROM admin_accounts WHERE admin_id = :id LIMIT 1");
    } else {
        $stmt = $pdo->prepare("SELECT driver_id AS id, email, 'driver' AS role 
                               FROM driver_accounts WHERE driver_id = :id LIMIT 1");
    }

    $stmt->execute([':id' => $reset['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    // 3. Generate new token & expiration
    $newToken = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

    $ins = $pdo->prepare("
        INSERT INTO password_resets (user_id, user_type, token, expires_at) 
        VALUES (:id, :type, :token, :expires_at)
    ");
    $ins->execute([
        ':id' => $user['id'],
        ':type' => $user['role'],
        ':token' => $newToken,
        ':expires_at' => $expiresAt
    ]);

    // 4. Generate new link
    if ($user['role'] === 'admin') {
        $link = "http://localhost/leilife/pages/admin/set-password-x9P2kL7zQ.php?token=" . urlencode($newToken);
    } else {
        $link = "http://localhost/leilife/pages/driver/set-password-8Xc1mB2.php?token=" . urlencode($newToken);
    }

    // 5. Send email
    if (sendResetLink($user['email'], $newToken, $link)) {
        echo json_encode(['success' => true, 'message' => 'A new reset link has been sent to your email']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send email']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
