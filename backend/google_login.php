<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/db_script/db.php';
require_once __DIR__ . '/db_script/env.php'; // wherever your loadEnv() function is defined

session_start();

// Load environment variables
loadEnv(__DIR__ . '/../.env'); // Adjust path based on your project structure

$GOOGLE_CLIENT_ID     = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
$GOOGLE_CLIENT_SECRET = $_ENV['GOOGLE_CLIENT_SECRET'] ?? '';
$GOOGLE_REDIRECT_URI  = $_ENV['GOOGLE_REDIRECT_URI'] ?? '';

error_reporting(E_ALL & ~E_DEPRECATED);


if (!$GOOGLE_CLIENT_ID || !$GOOGLE_CLIENT_SECRET || !$GOOGLE_REDIRECT_URI) {
    die("Google OAuth credentials not set in environment.");
}

// Configure Google Client
$client = new Google_Client();
$client->setClientId($GOOGLE_CLIENT_ID);
$client->setClientSecret($GOOGLE_CLIENT_SECRET);
$client->setRedirectUri($GOOGLE_REDIRECT_URI);
$client->addScope("email");
$client->addScope("profile");

// Step 1: Redirect to Google login
if (!isset($_GET['code'])) {
    $authUrl = $client->createAuthUrl();
    header("Location: $authUrl");
    exit;
}

// Step 2: Exchange auth code for access token
$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
if (isset($token['error'])) {
    die("Google Login failed: " . htmlspecialchars($token['error']));
}
$client->setAccessToken($token['access_token']);

// Step 3: Get user info from Google
$google_oauth = new Google_Service_Oauth2($client);
$google_account_info = $google_oauth->userinfo->get();

$email     = $google_account_info->email;
$google_id = $google_account_info->id;
$fullName  = $google_account_info->name;
$profile_picture  = $google_account_info->picture;


// Split name into first/last
$nameParts  = explode(" ", $fullName, 2);
$first_name = $nameParts[0] ?? '';
$last_name  = $nameParts[1] ?? '';
$username   = strtolower(preg_replace('/\s+/', '', $first_name)) . rand(100, 999);

try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);


    if ($user) {
        // Update profile picture if it's empty or different
        if (empty($user['profile_picture']) || $user['profile_picture'] !== $profile_picture) {
            $update = $pdo->prepare("UPDATE users SET profile_picture = :profile_picture WHERE user_id = :id");
            $update->execute([
                ':profile_picture' => $profile_picture, 
                ':id' => $user['user_id']
            ]);
        }


        if ($user['auth_provider'] === 'google') {
            // ✅ Existing Google user → log them in
            $_SESSION['profile_picture'] = $profile_picture;
            $_SESSION['user_id']  = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email']    = $user['email'];
        } elseif ($user['auth_provider'] === 'local') {
            // ❌ Local account exists → block Google login
            die("This email is already registered with a password. Please log in with your email and password.");
        }
    } else {
        // 🚀 New Google user → auto-register
        $stmt = $pdo->prepare("
            INSERT INTO users (username, first_name, last_name, email, password_hash, auth_provider, google_id, profile_picture) 
            VALUES (:username, :first_name, :last_name, :email, NULL, 'google', :google_id, :profile_picture)
        ");
        $stmt->execute([
            ':username'   => $username,
            ':first_name' => $first_name,
            ':last_name'  => $last_name,
            ':email'      => $email,
            ':google_id'  => $google_id,
            ':profile_picture' => $profile_picture

        ]);

        $new_user_id = $pdo->lastInsertId();
        $_SESSION['user_id']  = $new_user_id;
        $_SESSION['username'] = $username;
        $_SESSION['email']    = $email;
    }

    header("Location: /Leilife/public/index.php?page=home");
    exit;
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}
