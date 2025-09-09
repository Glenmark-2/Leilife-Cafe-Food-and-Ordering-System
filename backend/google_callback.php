<?php
session_start();
require_once __DIR__ . "/db_script/db.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['code'])) {
    echo "<script>alert('No Google authorization code received.'); window.location.href='/Leilife/public/index.php?page=login';</script>";
    exit;
}

$code = $_GET['code'];

// Exchange code for tokens
$tokenRequest = curl_init();
curl_setopt($tokenRequest, CURLOPT_URL, "https://oauth2.googleapis.com/token");
curl_setopt($tokenRequest, CURLOPT_POST, 1);
curl_setopt($tokenRequest, CURLOPT_POSTFIELDS, http_build_query([
    'code' => $code,
    'client_id' => "YOUR_CLIENT_ID",
    'client_secret' => "YOUR_CLIENT_SECRET",
    'redirect_uri' => "http://localhost/leilife/public/google_callback.php",
    'grant_type' => 'authorization_code'
]));
curl_setopt($tokenRequest, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($tokenRequest);
curl_close($tokenRequest);

$tokenData = json_decode($response, true);

if (!isset($tokenData['id_token'])) {
    echo "<script>alert('Failed to get Google ID token.'); window.location.href='/Leilife/public/index.php?page=login';</script>";
    exit;
}

// Decode ID token
$jwt = explode('.', $tokenData['id_token'])[1];
$userInfo = json_decode(base64_decode($jwt), true);

$google_id = $userInfo['sub'];
$email     = $userInfo['email'];
$fname     = $userInfo['given_name'] ?? '';
$lname     = $userInfo['family_name'] ?? '';
$name      = $userInfo['name'] ?? '';

// Helper to create unique username
function make_unique_username($pdo, $base) {
    $username = $base;
    $i = 1;
    while (true) {
        $stmt = $pdo->prepare("SELECT 1 FROM users WHERE username = :u LIMIT 1");
        $stmt->execute([':u' => $username]);
        if (!$stmt->fetch()) break;
        $username = $base . $i;
        $i++;
    }
    return $username;
}

try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Existing user → login
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['google_id'] = $google_id;

        header("Location: /Leilife/public/index.php?page=home");
        exit;
    }

    // New user → insert into users
    $base_username = strtolower(preg_replace('/\s+/', '', $fname . '.' . $lname));
    $username = make_unique_username($pdo, $base_username);

    $ins = $pdo->prepare("
        INSERT INTO users 
        (username, first_name, last_name, email, phone_number, password_hash) 
        VALUES 
        (:username, :fname, :lname, :email, NULL, '')
    ");

    $ins->execute([
        ':username' => $username,
        ':fname' => $fname,
        ':lname' => $lname,
        ':email' => $email
    ]);

    // ✅ Corrected error check for this insert
    var_dump($user); // see if the select found an existing user
var_dump($ins->errorInfo()); // see if insert had any error
die(); // stop execution so you can see output


    $user_id = $pdo->lastInsertId();

    // Start session
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['google_id'] = $google_id;

    // Redirect to set password page
    header("Location: /Leilife/public/index.php?page=set_password");
    exit;

} catch (PDOException $e) {
    echo "<script>alert('Database error: " . htmlspecialchars($e->getMessage()) . "'); window.history.back();</script>";
    exit;
}
