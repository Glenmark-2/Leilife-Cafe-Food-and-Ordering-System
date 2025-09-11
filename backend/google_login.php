<?php
// backend/google_login.php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

require_once __DIR__ . '/db_script/db.php';

// ✅ Require Composer (Google API Client)
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    error_log("Google login error: Missing vendor/autoload.php");
    http_response_code(500);
    exit("Server configuration error.");
}
require_once $autoloadPath;

use Google\Client as GoogleClient;

$client = new GoogleClient();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
$client->addScope("email");
$client->addScope("profile");

// --- Step 1: If no code, redirect to Google ---
if (!isset($_GET['code'])) {
    // Generate state to prevent CSRF
    $state = bin2hex(random_bytes(16));
    $_SESSION['oauth2_state'] = $state;

    $authUrl = $client->createAuthUrl() . "&state=" . urlencode($state);
    header("Location: " . $authUrl);
    exit;
}

// --- Step 2: Validate state ---
if (
    !isset($_GET['state'], $_SESSION['oauth2_state']) ||
    $_GET['state'] !== $_SESSION['oauth2_state']
) {
    unset($_SESSION['oauth2_state']);
    http_response_code(403);
    exit("Security validation failed. Please try again.");
}
unset($_SESSION['oauth2_state']);

// --- Step 3: Exchange code for token ---
try {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (isset($token['error'])) {
        error_log("Google token error: " . $token['error_description']);
        http_response_code(401);
        exit("Google login failed. Please try again.");
    }
    $client->setAccessToken($token);

    // --- Step 4: Get user info ---
    $oauth = new Google\Service\Oauth2($client);
    $googleUser = $oauth->userinfo->get();

    $googleId = $googleUser->id ?? null;
    $email    = strtolower(trim($googleUser->email ?? ''));
    $name     = $googleUser->name ?? '';
    $first    = $googleUser->givenName ?? '';
    $last     = $googleUser->familyName ?? '';

    if (!$googleId || !$email) {
        http_response_code(400);
        exit("Google account data incomplete.");
    }

    // --- Step 5: Check if user exists ---
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Block if registered via different provider
        if ($user['auth_provider'] !== 'google') {
            exit("This email is registered with another login method.");
        }
    } else {
        // --- Step 6: Register new Google user ---
        $stmt = $pdo->prepare("
            INSERT INTO users (username, first_name, last_name, email, auth_provider, google_id)
            VALUES (:username, :first, :last, :email, 'google', :google_id)
        ");
        $stmt->execute([
            ':username'  => $name,
            ':first'     => $first,
            ':last'      => $last,
            ':email'     => $email,
            ':google_id' => $googleId,
        ]);

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // --- Step 7: Create session ---
    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['user_id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['email']     = $user['email'];

    // ✅ Redirect to home
    header("Location: /Leilife/public/index.php?page=home");
    exit;

} catch (Exception $e) {
    error_log("Google login exception: " . $e->getMessage());
    http_response_code(500);
    exit("Unexpected error during Google login.");
}
