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

// ✅ Check logged-in user
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    jsonResponse(['success' => false, 'message' => 'Not authenticated.'], 401);
}

// ✅ Collect form data
$newPassword     = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// ✅ Validate
if ($newPassword !== $confirmPassword) {
    jsonResponse(['success' => false, 'message' => 'Passwords do not match.']);
}

if (strlen($newPassword) < 8) {
    jsonResponse(['success' => false, 'message' => 'Password must be at least 8 characters long.']);
}

// ✅ Hash password
$hash = password_hash($newPassword, PASSWORD_BCRYPT);

// ✅ Update database (note the `password_hash` column name!)
try {
    $stmt = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE user_id = :id");
    $stmt->execute([
        ':hash' => $hash,
        ':id'   => $userId
    ]);

    // update session flag if you track it
    $_SESSION['user_has_password'] = true;

    jsonResponse(['success' => true, 'message' => 'Password updated successfully!']);
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
