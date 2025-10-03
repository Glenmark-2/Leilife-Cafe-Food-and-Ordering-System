<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db_script/db.php';
require __DIR__ . '/send_mail.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
    exit;
}

$email = trim($_POST["email"] ?? '');

if (empty($email)) {
    echo json_encode(["success" => false, "message" => "Email is required."]);
    exit;
}

$pdo->exec("DELETE FROM password_resets WHERE expires_at < NOW() OR used = 1");
// Tables to check with user_type mapping
$tables = [
    ["table" => "users", "id_field" => "user_id", "type" => "user"],
    ["table" => "admin_accounts", "id_field" => "admin_id", "type" => "admin"],
    ["table" => "driver_accounts", "id_field" => "driver_id", "type" => "driver"]
];

$found = null;

foreach ($tables as $t) {
    $stmt = $pdo->prepare("SELECT {$t['id_field']} AS id FROM {$t['table']} WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $found = [
            "id" => $row['id'],
            "user_type" => $t['type']
        ];
        break;
    }
}

if ($found) {
    // Generate reset token
    $token = bin2hex(random_bytes(32));
    $expiresAt = gmdate("Y-m-d H:i:s", time() + 3600);

    // Insert reset record
    $insrt = $pdo->prepare("
        INSERT INTO password_resets (user_id, token, expires_at, user_type) 
        VALUES (:user_id, :token, :expires_at, :user_type)
    ");
    $insrt->execute([
        ':user_id'   => $found['id'],
        ':token'     => $token,
        ':expires_at'=> $expiresAt,
        ':user_type' => $found['user_type'], 
    ]);

    // Send reset email
    $link = "http://localhost/Leilife/public/index.php?page=forgot-password&token=" . urlencode($token);
    sendResetLink($email, $token, $link);

    echo json_encode(["success" => true, "message" => "Reset link sent."]);
} else {
    echo json_encode(["success" => false, "message" => "Email not found in any account."]);
}
?>
