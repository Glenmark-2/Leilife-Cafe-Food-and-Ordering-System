<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ Include DB connection
require_once __DIR__ . "/db_script/db.php";

session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (empty($email) || empty($password)) {
        echo "<script>alert('Please fill in all fields.'); window.history.back();</script>";
        exit;
    }

    try {
        // ✅ Find user by email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // 🚨 Block login if not verified
            if ((int)$user['is_verified'] === 0) {
                echo "<script>alert('Please verify your email before logging in.'); window.location.href='/Leilife/public/index.php?page=verify_notice';</script>";
                exit;
            }

            // ✅ Verified user → allow login
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];

            header("Location: /Leilife/public/index.php?page=home");
            exit;

        } else {
            echo "<script>alert('Invalid email or password'); window.history.back();</script>";
            exit;
        }

    } catch (PDOException $e) {
        echo "<script>alert('Database error: " . $e->getMessage() . "'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Invalid request method'); window.history.back();</script>";
}
