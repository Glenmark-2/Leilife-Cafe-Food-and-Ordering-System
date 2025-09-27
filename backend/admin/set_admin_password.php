<?php 
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../db_script/db.php';

header('Content-Type: application/json');

function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
}

// Get input
$token            = $_POST['token'] ?? '';
$new_password     = trim($_POST['new_password'] ?? '');
$confirm_password = trim($_POST['confirm_password'] ?? '');

if (!$token) {
    jsonResponse(['success' => false, 'message' => 'Missing link.'], 400);
}

// Empty or only-spaces check
if ($new_password === '' || $confirm_password === '') {
    jsonResponse(['success' => false, 'message' => 'Password cannot be empty or only spaces.']);
}

// Check password confirmation
if ($new_password !== $confirm_password) {
    jsonResponse(['success' => false, 'message' => 'Passwords do not match.']);
}

// Password strength & spaces validation
if (strlen($new_password) < 8) {
    jsonResponse(['success' => false, 'message' => 'Password must be at least 8 characters long.']);
}
if (preg_match('/\s/', $new_password)) {
    jsonResponse(['success' => false, 'message' => 'Password cannot contain spaces.']);
}
if (preg_match('/\s{2,}/', $new_password)) {
    jsonResponse(['success' => false, 'message' => 'Password cannot contain consecutive spaces.']);
}

$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Verify reset link in DB
$stmt = $pdo->prepare("SELECT user_id, expires_at FROM password_resets WHERE token = :token LIMIT 1");
$stmt->execute([':token' => $token]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

// Token not found
if (!$result) {
    jsonResponse(['success' => false, 'message' => 'Password may have already been changed.'], 400); // token not found
}

// Check token expiration
if (strtotime($result['expires_at']) < time()) {
    jsonResponse(['success' => false, 'message' => 'This link has expired.'], 400); // token expired
}

$admin_id = $result['user_id'];

// Update admin password
$update = $pdo->prepare("UPDATE admin_accounts SET password = :password WHERE admin_id = :id");
$success = $update->execute([
    ':password' => $hashed_password,
    ':id' => $admin_id,
]);

if ($success) {
    // Delete token so it can’t be reused
    $pdo->prepare("DELETE FROM password_resets WHERE token = :token")->execute([':token' => $token]);

    jsonResponse(['success' => true, 'message' => 'Password updated successfully.']);
} else {
    jsonResponse(['success' => false, 'message' => 'Failed to update password.'], 500);
}
