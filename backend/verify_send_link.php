<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db_script/db.php';

$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$token = trim($data['token'] ?? ($_POST['token'] ?? ''));
$new_password = trim($data['password'] ?? ($_POST['new_password'] ?? ''));


// Validate input
if (empty($token) || empty($new_password)) {
  error_log("DEBUG INPUT: " . file_get_contents("php://input"));
error_log("DEBUG PARSED: " . print_r($data, true));

    http_response_code(400);
    echo json_encode(["error" => "Token and password are required"]);
    exit;
}

// Step 1: Validate token
$stmt = $pdo->prepare("
    SELECT user_id 
    FROM password_resets
    WHERE token = :token 
      AND expires_at > CURRENT_TIMESTAMP
      AND used = 0
    LIMIT 1
");
$stmt->execute([':token' => $token]);
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid or expired token"]);
    exit;
}

$user_id = $reset['user_id'];

// Step 2: Update password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$updateUser = $pdo->prepare("UPDATE users SET password_hash = :password WHERE user_id = :id");
$updateUser->execute([':password' => $hashed_password, ':id' => $user_id]);

// Step 3: Mark token as used
$updateToken = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = :token");
$updateToken->execute([':token' => $token]);

echo json_encode(["success" => true]);


// todo: if token does not exist throw error
// if token exist 
  //  SELECT user_id from password_resest where expires_at <= current_timestamp() and used = 0 and token = :token;
  //  update password_reset set used = 1 where user_id = :user_id;
  //  update password_reset set used = 1 where user_id = :user_id; 