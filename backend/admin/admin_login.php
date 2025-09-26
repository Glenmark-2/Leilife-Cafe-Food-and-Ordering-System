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
        header('Location: /leilife/pages/admin/login-x9P2kL7zQ.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM admin_accounts WHERE email = :input OR username = :input");
    $stmt->execute(['input' => $emailOrUsername]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        if ((int)$admin['is_active'] === 0) {
            $_SESSION['login_error'] = 'This account is temporarily deactivated. Please contact the system administrator.';
            header('Location: /leilife/pages/admin/login-x9P2kL7zQ.php');
            exit;
        }

        // Verify password
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['show_welcome'] = true;

            header('Location: /leilife/public/admin.php?page=dashboard');
            exit;
        }
    }

    // If invalid
    $_SESSION['login_error'] = 'Invalid email/username or password.';
    header('Location: /leilife/pages/admin/login-x9P2kL7zQ.php');
    exit;
}
