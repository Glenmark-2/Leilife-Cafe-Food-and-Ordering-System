<?php
session_start();
require_once __DIR__ . "/../db_script/db.php";
require_once "../send_mail.php";

header('Content-Type: application/json');

function respond($success, $message) {
    echo json_encode(["success" => $success, "message" => $message]);
    exit;
}

try {
    $name     = trim($_POST['name'] ?? '');
    $role     = trim($_POST['role'] ?? '');
    $shift    = trim($_POST['shift'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$name || !$role || !$shift || !$username || !$email || !$password) {
        throw new Exception("All fields are required.");
    }

    function emailExists($pdo, $email, $table, $column = 'email') {
        $allowedTables = ['users', 'admin_accounts', 'driver_accounts'];
        if (!in_array($table, $allowedTables)) {
            throw new Exception("Invalid table name.");
        }
        $stmt = $pdo->prepare("SELECT 1 FROM {$table} WHERE {$column} = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        return $stmt->fetchColumn();
    }

    if (emailExists($pdo, $email, 'users'))  respond(false, "Email already registered in Users.");
    if (emailExists($pdo, $email, 'admin_accounts'))  respond(false, "Email already registered as Admin.");
    if (emailExists($pdo, $email, 'driver_accounts')) respond(false, "Email already registered as Driver.");

    $otp = generateOTP();

    $_SESSION['pending_account'] = [
        'name'     => $name,
        'role'     => $role,
        'shift'    => $shift,
        'username' => $username,
        'email'    => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'photo'    => $_FILES['photo']['name'] ?? null,
        'otp'      => $otp,
        'expires'  => time() + 300
    ];

    if (!sendOTP($email, $otp)) {
        throw new Exception("Failed to send OTP.");
    }

    respond(true, "OTP sent. Verification required.");
} catch (Exception $e) {
    respond(false, $e->getMessage());
}
