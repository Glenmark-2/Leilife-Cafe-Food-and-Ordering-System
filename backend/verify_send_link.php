<?php
declare(strict_types=1);

// --- DEV ONLY: Enable errors for debugging ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// --- Always return JSON on fatal errors ---
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Server fatal error: " . $error['message'],
            "type" => $error['type'],
            "file" => $error['file'],
            "line" => $error['line']
        ]);
        exit;
    }
});

try {
    require_once __DIR__ . '/db_script/db.php';

    // Parse JSON body
    $data = json_decode(file_get_contents("php://input"), true);

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Method not allowed"]);
        exit;
    }

    $token = trim($data['token'] ?? ($_POST['token'] ?? ''));
    $new_password = trim($data['password'] ?? ($_POST['new_password'] ?? ''));

    if (empty($token) || empty($new_password)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Token and password are required"]);
        exit;
    }

    // --- Step 1: Validate token ---
    $stmt = $pdo->prepare("
        SELECT user_id, user_type
        FROM password_resets
        WHERE token = :token
          AND expires_at > CURRENT_TIMESTAMP
        LIMIT 1
    ");
    $stmt->execute([':token' => $token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Invalid or expired token"]);
        exit;
    }

    $user_id   = $reset['user_id'];
    $user_type = $reset['user_type'];

    // --- Step 2: Update password ---
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    switch ($user_type) {
        case 'user':
            $updateUser = $pdo->prepare("UPDATE users SET password_hash = :password WHERE user_id = :id");
            break;
        case 'admin':
            $updateUser = $pdo->prepare("UPDATE admin_accounts SET password = :password WHERE admin_id = :id");
            break;
        case 'driver':
            $updateUser = $pdo->prepare("UPDATE driver_accounts SET password = :password WHERE driver_id = :id");
            break;
        default:
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Unknown account type"]);
            exit;
    }

    $updateUser->execute([':password' => $hashed_password, ':id' => $user_id]);

    // --- Step 3: Delete token after use ---
    $deleteToken = $pdo->prepare("DELETE FROM password_resets WHERE token = :token");
    $deleteToken->execute([':token' => $token]);

    echo json_encode(["success" => true, "message" => "Password updated successfully"]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Server error: " . $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ]);
    exit;
}
