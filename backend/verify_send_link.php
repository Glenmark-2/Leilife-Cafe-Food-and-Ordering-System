<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db_script/db.php';

try {
    $data = json_decode(file_get_contents("php://input"), true);

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Method not allowed"]);
        exit;
    }

    $token = trim($data['token'] ?? '');
    $new_password = trim($data['password'] ?? '');

    if (empty($token) || empty($new_password)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Token and password are required"]);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT user_id, user_type
        FROM password_resets
        WHERE token = :token
          AND expires_at > UTC_TIMESTAMP()
          AND used = 0
        LIMIT 1
    ");
    $stmt->execute([':token' => $token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Link expired."]);
        exit;
    }

    $user_id   = $reset['user_id'];
    $user_type = $reset['user_type'];

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

    // --- Step 4: Mark token as used ---
    $markUsed = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = :token");
    $markUsed->execute([':token' => $token]);

    echo json_encode(["success" => true, "message" => "Password updated successfully"]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Server error: " . $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ]);
}
