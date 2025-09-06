<?php
// backend/verify.php
// Verify token, check expiry, move user to `users` if valid

if (!isset($pdo)) {
    // prefer db init in backend/db_script
    if (file_exists(__DIR__ . '/db_script/init.php')) {
        require_once __DIR__ . '/db_script/init.php';
    } elseif (file_exists(__DIR__ . '/db.php')) {
        require_once __DIR__ . '/db.php';
    } else {
        @include_once __DIR__ . '/../backend/db_script/init.php';
    }
}

// Redirect destinations
$redirectSuccess = "/Leilife/public/index.php?page=verify_success";
$redirectFail    = "/Leilife/public/index.php?page=verify_failed";
$redirectExpired = "/Leilife/public/index.php?page=verify_expired";

// Ensure token exists
if (!isset($_GET['token']) || empty($_GET['token'])) {
    header("Location: $redirectFail");
    exit;
}

$token = $_GET['token'];

// Find pending registration
$stmt = $pdo->prepare("SELECT * FROM user_registrations WHERE verification_token = ?");
$stmt->execute([$token]);
$registration = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$registration) {
    // token not found
    header("Location: $redirectFail");
    exit;
}

// Check expiry
$expires = strtotime($registration['expires_at']);
if ($expires === false || time() > $expires) {
    // token expired
    header("Location: $redirectExpired&email=" . urlencode($registration['email']));
    exit;
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

    header("Location: $redirectSuccess");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Verification error: " . $e->getMessage());
    header("Location: $redirectFail");
    exit;
}
