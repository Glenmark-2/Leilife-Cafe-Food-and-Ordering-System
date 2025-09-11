<?php
// backend/verify.php
// Verify token, check expiry, move user to `users` if valid

declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Start buffering to prevent "headers already sent"
ob_start();

// DB connection
if (!isset($pdo)) {
    if (file_exists(__DIR__ . '/db_script/init.php')) {
        require_once __DIR__ . '/db_script/init.php';
    } elseif (file_exists(__DIR__ . '/db.php')) {
        require_once __DIR__ . '/db.php';
    } else {
        @include_once __DIR__ . '/../backend/db_script/init.php';
    }
}

// Central redirect helper
function safe_redirect(string $url): void {
    if (!headers_sent()) {
        header("Location: " . $url);
        exit;
    }
    echo "<script>window.location.href='" . htmlspecialchars($url, ENT_QUOTES) . "';</script>";
    exit;
}

// Redirect destinations
$redirectSuccess = "/Leilife/public/index.php?page=verify_success";
$redirectFail    = "/Leilife/public/index.php?page=verify_failed";
$redirectExpired = "/Leilife/public/index.php?page=verify_expired";

// Ensure token exists
if (empty($_GET['token'])) {
    safe_redirect($redirectFail);
}

$token = $_GET['token'];

// Find pending registration
$stmt = $pdo->prepare("SELECT * FROM user_registrations WHERE verification_token = ?");
$stmt->execute([$token]);
$registration = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$registration) {
    safe_redirect($redirectFail);
}

// Check expiry
$expires = strtotime($registration['expires_at']);
if ($expires === false || time() > $expires) {
    safe_redirect($redirectExpired . "&email=" . urlencode($registration['email']));
}

try {
    $pdo->beginTransaction();

    // Insert into users table
    $insert = $pdo->prepare("
        INSERT INTO users (username, first_name, last_name, email, phone_number, password_hash)
        VALUES (:username, :first_name, :last_name, :email, :phone_number, :password_hash)
    ");
    $insert->execute([
        ':username'      => $registration['username'],
        ':first_name'    => $registration['first_name'],
        ':last_name'     => $registration['last_name'],
        ':email'         => $registration['email'],
        ':phone_number'  => $registration['phone_number'],
        ':password_hash' => $registration['password_hash']
    ]);

    // Delete from user_registrations
    $delete = $pdo->prepare("DELETE FROM user_registrations WHERE reg_id = ?");
    $delete->execute([$registration['reg_id']]);

    $pdo->commit();

    safe_redirect($redirectSuccess);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Verification error: " . $e->getMessage());
    safe_redirect($redirectFail);
}

ob_end_flush();
