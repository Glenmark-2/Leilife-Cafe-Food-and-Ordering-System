<?php 
declare(strict_types=1);
session_start();

require_once __DIR__ . '/db_script/db.php';

header('Content-Type: application/json');

function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// ✅ Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
}

if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    respond(false, ["Security check failed. Please try again."]);
}

// ✅ Check session
if (!isset($_SESSION['user_id'])) {
    jsonResponse(['success' => false, 'message' => 'User not logged in.'], 401);
}

$id = $_SESSION['user_id'];

// ✅ Get input
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// ✅ Check password confirmation
if ($new_password !== $confirm_password) {
    jsonResponse(['success' => false, 'message' => 'Passwords do not match.']);
}

// ✅ Hash password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// ✅ Check if user exists
$stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_id = :id");
$stmt->execute([':id' => $id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    jsonResponse(['success' => false, 'message' => 'User not found.'], 404);
}

// ✅ Update password
$update = $pdo->prepare("UPDATE users SET password_hash = :password WHERE user_id = :id");
$success = $update->execute([
    ':password' => $hashed_password,
    ':id' => $id,
]);

if ($success) {
    jsonResponse(['success' => true, 'message' => 'Password updated successfully.']);
} else {
    jsonResponse(['success' => false, 'message' => 'Failed to update password.'], 500);
}
