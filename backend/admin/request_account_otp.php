
<?php
session_start();
require_once "../send_mail.php";

header('Content-Type: application/json');

try {
    $name = trim($_POST['name'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $shift = trim($_POST['shift'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$name || !$role || !$shift || !$username || !$email || !$password) {
        throw new Exception("All fields are required.");
    }

    // Generate OTP
    $otp = generateOTP();

    // Store user data + otp in session
    $_SESSION['pending_account'] = [
        'name' => $name,
        'role' => $role,
        'shift' => $shift,
        'username' => $username,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT), // store hash
        'photo' => $_FILES['photo']['name'] ?? null,
        'otp' => $otp,
        'expires' => time() + 300 // 5 minutes
    ];

    if (!sendOTP($email, $otp)) {
        throw new Exception("Failed to send OTP.");
    }

    echo json_encode(["success" => true, "otp_required" => true]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
