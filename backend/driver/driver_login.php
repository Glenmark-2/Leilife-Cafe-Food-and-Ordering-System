<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once __DIR__ . '/../db_script/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailOrUsername = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!$emailOrUsername || !$password) {
        $_SESSION['login_error'] = 'Please fill in all fields.';
        header('Location: /leilife/pages/driver/login-driver-8Xc1mB2.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM driver_accounts WHERE email = :input OR username = :input");
    $stmt->execute(['input' => $emailOrUsername]);
    $driver = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($driver) {
        if ((int)$driver['is_active'] === 0) {
            $_SESSION['login_error'] = 'This account is temporarily deactivated. Please contact the system administrator.';
            header('Location: /leilife/pages/driver/login-driver-8Xc1mB2.php');
            exit;
        }

        // Verify password
        if (password_verify($password, $driver['password'])) {
            $_SESSION['driver_id'] = $driver['driver_id'];
            $_SESSION['driver_name'] = $driver['full_name'];
            $_SESSION['driver_email'] = $driver['email'];
            $_SESSION['show_welcome'] = true;

            // ✅ Redirect to driver section inside admin.php
            header('Location: /leilife/public/driver.php?page=home');
            exit;
        }
    }

    // If invalid
    $_SESSION['login_error'] = 'Invalid email/username or password.';
    header('Location: /leilife/pages/driver/login-driver-8Xc1mB2.php');
    exit;
}
