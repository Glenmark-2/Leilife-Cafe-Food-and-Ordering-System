<?php
// backend/resend_verification.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/db_script/db.php";
require_once __DIR__ . "/send_mail.php"; // ✅ use your PHPMailer function

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? '');

    if (empty($email)) {
        die("Email is required.");
    }

    // 1. Look for user in user_registrations (not users, since unverified accounts live here)
    $stmt = $pdo->prepare("SELECT reg_id, expires_at FROM user_registrations WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "<script>alert('No pending registration found for this email.'); window.history.back();</script>";
        exit;
    }

    // 2. Generate new token + expiry
    $token = bin2hex(random_bytes(16));
    $sent_at = date('Y-m-d H:i:s');
    $expires_at = date('Y-m-d H:i:s', strtotime("+1 hour"));

    // 3. Update record
    $upd = $pdo->prepare("UPDATE user_registrations 
                          SET verification_token = :token, verification_sent_at = :sent_at, expires_at = :expires_at
                          WHERE reg_id = :id");
    $upd->execute([
        ':token' => $token,
        ':sent_at' => $sent_at,
        ':expires_at' => $expires_at,
        ':id' => $user['reg_id']
    ]);

    // 4. Send verification email via Ethereal (same as signup)
    if (sendVerificationEmail($email, $token)) {
        echo "<script>alert('A new verification link has been sent to your email.'); 
              window.location.href='/Leilife/public/index.php?page=login';</script>";
        exit;
    } else {
        echo "<script>alert('Error: Failed to send verification email. Please try again later.'); 
              window.history.back();</script>";
        exit;
    }
}
