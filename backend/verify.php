<?php
// backend/verify.php
// Verify token and redirect to success / failed pages.

// If $pdo is not set (coming direct), try to load DB init. When routed through public/index.php,
// init.php was already loaded so this block will be skipped.
if (!isset($pdo)) {
    // prefer the db init located in backend/db_script
    if (file_exists(__DIR__ . '/db_script/init.php')) {
        require_once __DIR__ . '/db_script/init.php';
    } elseif (file_exists(__DIR__ . '/db.php')) {
        require_once __DIR__ . '/db.php';
    } else {
        // last resort: try parent dir (if paths differ)
        @include_once __DIR__ . '/../backend/db_script/init.php';
    }
}

// destinations
$redirectSuccess = "/Leilife/public/index.php?page=verify_success";
$redirectFail    = "/Leilife/public/index.php?page=verify_failed"; // create this page or use 404

// Ensure token exists
if (!isset($_GET['token']) || empty($_GET['token'])) {
    header("Location: $redirectFail");
    exit;
}

$token = $_GET['token'];

// Find user with this token
$stmt = $pdo->prepare("SELECT user_id, verification_sent_at FROM users WHERE verification_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // token missing/invalid
    header("Location: $redirectFail");
    exit;
}

// Optional: check expiry (uncomment & adjust hours if you want expiration)
/*
$sent = strtotime($user['verification_sent_at']);
$hoursValid = 24;
if ($sent === false || (time() - $sent) > ($hoursValid * 3600)) {
    // token expired
    header("Location: $redirectFail");
    exit;
}
*/

// Mark verified
$update = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL, verification_sent_at = NULL WHERE user_id = ?");
$ok = $update->execute([$user['user_id']]);

if ($ok) {
    header("Location: $redirectSuccess");
    exit;
} else {
    // update failed for some reason
    header("Location: $redirectFail");
    exit;
}
