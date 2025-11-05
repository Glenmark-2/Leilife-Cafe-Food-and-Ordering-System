<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db_script/db.php';
require_once __DIR__ . '/send_mail.php';

$response = ["success" => true, "message" => "If that email exists, a reset link has been sent."];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $response = ["success" => false, "message" => "Invalid request."];
    echo json_encode($response);
    exit;
}

$email = trim($_POST["email"] ?? '');
if (empty($email)) {
    $response = ["success" => false, "message" => "Email is required."];
    echo json_encode($response);
    exit;
}

$pdo->exec("DELETE FROM password_resets WHERE expires_at < UTC_TIMESTAMP() OR used = 1");

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
        $found = ["id" => $row['id'], "user_type" => $t['type']];
        break;
    }
}

if ($found) {
    $resendLimit = 3;
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM password_resets
        WHERE user_id = :id
          AND created_at > (UTC_TIMESTAMP() - INTERVAL 24 HOUR)
    ");
    $stmt->execute([':id' => $found['id']]);
    $sentToday = (int)$stmt->fetchColumn();

    if ($sentToday >= $resendLimit) {
        echo json_encode($response);
        exit;
    } else {
        $token = bin2hex(random_bytes(32));
        $createdAt = gmdate("Y-m-d H:i:s");
        $expiresAt = gmdate("Y-m-d H:i:s", time() + 3600);

        $insrt = $pdo->prepare("
            INSERT INTO password_resets (user_id, token, created_at, expires_at, user_type) 
            VALUES (:user_id, :token, :created_at, :expires_at, :user_type)
        ");
        $insrt->execute([
            ':user_id'    => $found['id'],
            ':token'      => $token,
            ':created_at' => $createdAt,
            ':expires_at' => $expiresAt,
            ':user_type'  => $found['user_type'],
        ]);

        $link = "http://localhost/Leilife/public/index.php?page=forgot-password&token=" . urlencode($token);
        sendResetLink($email, $token, $link);
    }
}

// ✅ Only echo JSON once at the end
echo json_encode($response);
exit;
