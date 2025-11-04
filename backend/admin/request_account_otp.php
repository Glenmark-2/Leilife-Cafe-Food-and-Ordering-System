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
    // ✅ Resend OTP request
    if (isset($_POST['resend']) && $_POST['resend'] == true) {
        if (!isset($_SESSION['pending_account']['email'])) {
            throw new Exception("No pending account found.");
        }

        $email = $_SESSION['pending_account']['email'];

        // Generate new OTP
        $otp = rand(100000, 999999);
        $_SESSION['pending_account']['otp'] = $otp;
        $_SESSION['pending_account']['expires'] = time() + 20; // 5 minutes

        if (!sendOTP($email, $otp)) {
            throw new Exception("Failed to send OTP.");
        }

        respond(true, "OTP resent successfully.");
    }

    // --- Normal new account request ---
    $name     = trim($_POST['name'] ?? '');
    $role     = trim($_POST['role'] ?? '');
    $shift    = trim($_POST['shift'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$name || !$role || !$shift || !$username || !$email || !$password) {
        throw new Exception("All fields are required.");
    }

    // --- Check email uniqueness ---
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

    // --- Handle photo upload ---
    $imageName = null;
    if (!empty($_FILES['photo']['name'])) {
        $uploadDir = __DIR__ . '/../../public/staffs/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $cleanFileName = preg_replace("/[^A-Za-z0-9.\-_]/", "_", trim($_FILES['photo']['name']));
        $imageName = time() . "_" . $cleanFileName;
        $targetFile = $uploadDir . $imageName;

        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
            respond(false, "Image upload failed");
        }
    }

    // --- Generate OTP and save to session ---
    $otp = rand(100000, 999999);
    $_SESSION['pending_account'] = [
        'name'     => $name,
        'role'     => $role,
        'shift'    => $shift,
        'username' => $username,
        'email'    => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'photo'    => $imageName, // saved filename
        'otp'      => $otp,
        'expires'  => time() + 20 // 5 minutes
    ];

    // --- Send OTP ---
    if (!sendOTP($email, $otp)) {
        respond(false, "Failed to send OTP.");
    }

    respond(true, "OTP sent. Verification required.");

} catch (Exception $e) {
    respond(false, $e->getMessage());
}
