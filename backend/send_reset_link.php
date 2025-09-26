<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db_script/db.php';
require __DIR__ .'/send_mail.php';

$data = json_decode(file_get_contents("php://input"),true);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    
    return;
}

if (isset($_POST["email"])) {
    $email = trim($_POST["email"] ?? NULL);

    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);


    if ($result) {
        // email exists
        $token = bin2hex(random_bytes(32));
        
        date_default_timezone_set('UTC');
        $expiresAt = gmdate("Y-m-d H:i:s", time() + 3600);

        $insrt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) 
                                VALUES (:user_id, :token, :expires_at)");
        $insrt->execute([
            ':user_id' => $result['user_id'],
            ':token' => $token,
            ':expires_at' => $expiresAt,
        ]);

        $link = "http://localhost/Leilife/public/index.php?page=forgot-password&token=" . urlencode($token);
        sendResetLink($email, $token,$link);

        //TODO: insert password_resets
        //TODO: call the send mail to send the token
        echo '{"success": true}';
    } else {
    
        // email not found
    }
}
?>