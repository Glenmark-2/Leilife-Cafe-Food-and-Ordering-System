<?php
// backend/signup.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/db_script/db.php";
require_once __DIR__ . "/send_mail.php"; // should expose sendVerificationEmail($email, $token)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<script>alert('Invalid request method.'); window.history.back();</script>";
    exit;
}

// Collect + sanitize
$fname    = trim($_POST['fname'] ?? '');
$lname    = trim($_POST['lname'] ?? '');
$email    = trim($_POST['email'] ?? '');
$phone    = trim($_POST['phone_number'] ?? '');
$password = trim($_POST['password'] ?? '');
$confirm  = trim($_POST['confirm_password'] ?? '');
$terms    = isset($_POST['terms']);

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

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('Please enter a valid email address.'); window.history.back();</script>";
    exit;
}

// Hash password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// base username (simple). We'll ensure uniqueness below.
$base_username = strtolower(preg_replace('/\s+/', '', $fname . '.' . $lname));

// helper to create a unique username across users + registrations
function make_unique_username($pdo, $base) {
    $username = $base;
    $i = 1;
    while (true) {
        $stmt = $pdo->prepare("
            SELECT 1 FROM users WHERE username = :u
            UNION
            SELECT 1 FROM user_registrations WHERE username = :u
            LIMIT 1
        ");
        $stmt->execute([':u' => $username]);
        if (!$stmt->fetch()) break;
        $username = $base . $i;
        $i++;
    }
    return $username;
}

try {
    // 1) If email already exists in users -> reject
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        echo "<script>alert('Email already registered. Please login or reset your password.'); window.history.back();</script>";
        exit;
    }

    // 2) Check if there is already a pending registration
    $stmt = $pdo->prepare("SELECT reg_id, expires_at FROM user_registrations WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $pending = $stmt->fetch(PDO::FETCH_ASSOC);

    $token = bin2hex(random_bytes(16));
    $sent_at = date('Y-m-d H:i:s');
    $expires_at = date('Y-m-d H:i:s', time() + 10); // 1 hour

    if ($pending) {
        // If pending and not expired -> update token and resend
        if (strtotime($pending['expires_at']) > time()) {
            $upd = $pdo->prepare("UPDATE user_registrations 
                                  SET verification_token = :token, verification_sent_at = :sent_at, expires_at = :expires_at
                                  WHERE reg_id = :id");
            $upd->execute([
                ':token' => $token,
                ':sent_at' => $sent_at,
                ':expires_at' => $expires_at,
                ':id' => $pending['reg_id']
            ]);

            if (sendVerificationEmail($email, $token)) {
                header("Location: /Leilife/public/index.php?page=verify_notice");
                exit;
            } else {
                echo "<script>alert('Could not send verification email. Contact support.'); window.history.back();</script>";
                exit;
            }
        } else {
            // expired -> delete and continue to insert new below
            $del = $pdo->prepare("DELETE FROM user_registrations WHERE reg_id = :id");
            $del->execute([':id' => $pending['reg_id']]);
        }
    }

    // 3) Create a unique username and insert into registrations
    $username = make_unique_username($pdo, $base_username);

    $ins = $pdo->prepare("INSERT INTO user_registrations
        (username, first_name, last_name, email, phone_number, password_hash, verification_token, verification_sent_at, expires_at)
        VALUES (:username, :fname, :lname, :email, :phone, :password_hash, :token, :sent_at, :expires_at)
    ");
    $ins->execute([
        ':username' => $username,
        ':fname' => $fname,
        ':lname' => $lname,
        ':email' => $email,
        ':phone' => $phone,
        ':password_hash' => $password_hash,
        ':token' => $token,
        ':sent_at' => $sent_at,
        ':expires_at' => $expires_at
    ]);

    // Send verification email
    if (sendVerificationEmail($email, $token)) {
        header("Location: /Leilife/public/index.php?page=verify_notice");
        exit;
    } else {
        echo "<script>alert('Signup saved but verification email could not be sent. Contact support.'); window.location.href='/Leilife/public/index.php?page=home';</script>";
        exit;
    }

} catch (PDOException $e) {
    // Handle duplicate email / unique constraint more gracefully
    if ($e->getCode() == 23000) { // integrity constraint violation (duplicate)
        echo "<script>alert('That email or username is already taken. Try a different one.'); window.history.back();</script>";
        exit;
    }
    // For debugging (you already enable errors). In production log this instead.
    echo "<script>alert('Database error: " . htmlspecialchars($e->getMessage()) . "'); window.history.back();</script>";
    exit;
}
