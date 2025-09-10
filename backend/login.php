<?php
// backend/login.php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . "/db_script/db.php";

session_start();

// Force JSON output
header("Content-Type: application/json");

// Only allow POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "errors"  => ["Invalid request method."]
    ]);
    exit;
}

// CSRF validation
if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    $_POST['csrf_token'] !== $_SESSION['csrf_token']
) {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "errors"  => ["Security validation failed. Please refresh and try again."]
    ]);
    exit;
}

// Collect + sanitize
$email    = strtolower(trim($_POST["email"] ?? ''));
$password = trim($_POST["password"] ?? '');

$errors = [];

// Validation
if (empty($email) || empty($password)) {
    $errors[] = "Please fill in all fields.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address.";
}

if ($errors) {
    echo json_encode(["success" => false, "errors" => $errors]);
    exit;
}

try {
    // Fetch only needed fields
    $stmt = $pdo->prepare("
        SELECT user_id, username, email, password_hash, auth_provider
        FROM users WHERE email = :email LIMIT 1
    ");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Generic error for all failures (avoid enumeration)
    $invalidLogin = ["success" => false, "errors" => ["Invalid email or password."]];

    if (!$user) {
        echo json_encode($invalidLogin);
        exit;
    }

    // Block wrong provider
    if ($user['auth_provider'] !== 'local') {
        echo json_encode([
            "success" => false,
            "errors"  => ["Please use the correct login method for this account."]
        ]);
        exit;
    }

    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        echo json_encode($invalidLogin);
        exit;
    }

    // ✅ Successful login
    session_regenerate_id(true); // prevent session fixation
    $_SESSION['user_id']   = $user['user_id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['email']     = $user['email'];

    // Return JSON redirect path
    echo json_encode([
        "success"  => true,
        "redirect" => "/Leilife/public/index.php?page=home"
    ]);
    exit;

} catch (PDOException $e) {
    // Log internally, generic message for client
    error_log("Login DB error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "errors"  => ["Something went wrong. Please try again later."]
    ]);
    exit;
}
