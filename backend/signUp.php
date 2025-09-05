<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/db_script/db.php"; 
require_once __DIR__ . "/send_mail.php"; // our mailer function

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fname    = trim($_POST["fname"]);
    $lname    = trim($_POST["lname"]);
    $email    = trim($_POST["email"]);
    $phone    = trim($_POST["phone_number"]);
    $password = trim($_POST["password"]);
    $confirm  = trim($_POST["confirm_password"]);
    $terms    = isset($_POST['terms']) ? true : false;

    // ✅ Validate
    if (!$terms) {
        echo "<script>alert('You must accept the Terms & Conditions.'); window.history.back();</script>";
        exit;
    }

    if ($password !== $confirm) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit;
    }

    if (empty($fname) || empty($lname) || empty($email) || empty($password)) {
        echo "<script>alert('Please fill in all required fields!'); window.history.back();</script>";
        exit;
    }

    // ✅ Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $username = strtolower($fname . "." . $lname);

    // ✅ Generate token
    $token = bin2hex(random_bytes(16));

    try {
        // Insert with token + is_verified = 0
        $stmt = $pdo->prepare("INSERT INTO users 
            (username, first_name, last_name, email, phone_number, password_hash, is_verified, verification_token, verification_sent_at) 
            VALUES (:username, :fname, :lname, :email, :phone, :password_hash, 0, :token, NOW())");

        $stmt->execute([
            ':username'      => $username,
            ':fname'         => $fname,
            ':lname'         => $lname,
            ':email'         => $email,
            ':phone'         => $phone,
            ':password_hash' => $password_hash,
            ':token'         => $token
        ]);

        // ✅ Send verification email
        if (sendVerificationEmail($email, $token)) {
           header("Location: /Leilife/public/index.php?page=verify_notice");
           exit;
        }else {
            echo "<script>alert('Signup successful, but email could not be sent. Contact support.'); window.location.href='/Leilife/public/index.php?page=home';</script>";
            exit;
        }

    } catch (PDOException $e) {
        echo "<script>alert('Database error: " . $e->getMessage() . "'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Invalid request method.'); window.history.back();</script>";
}
