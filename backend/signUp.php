<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . "/db_script/db.php";
require_once __DIR__ . "/send_mail.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function wants_json(): bool {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        return true;
    }
    if (!empty($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        return true;
    }
    return false;
}

function json_response(array $data, int $status = 200): void {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function respond(bool $success, array $errors = [], ?string $redirect = null): void {
    if (wants_json()) {
        if ($success) {
            json_response(['success' => true, 'redirect' => $redirect]);
        } else {
            json_response(['success' => false, 'errors' => $errors], 400);
        }
    } else {
        if (!$success) {
            $_SESSION['signup_errors'] = $errors;
            header("Location: /Leilife/public/index.php?page=signUp");
        } else {
            header("Location: " . $redirect);
        }
        exit;
    }
}

/**
 * Send the success response immediately to the client but do NOT exit the script.
 * After this function returns, the script can continue doing background work
 * (like sending email). Uses fastcgi_finish_request() when available.
 */
function send_immediate_success_and_continue(?string $redirect = null): void {
    if (wants_json()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(200);
        echo json_encode(['success' => true, 'redirect' => $redirect]);
    } else {
        header("Location: " . $redirect);
    }

    // flush output to client so browser can redirect; continue running afterwards
    ignore_user_abort(true);

    if (function_exists('fastcgi_finish_request')) {
        // Best option when using PHP-FPM / fastcgi
        fastcgi_finish_request();
    } else {
        // Fallback: attempt to flush buffers
        // End all output buffers if any, then flush
        while (ob_get_level() > 0) {
            @ob_end_flush();
        }
        @flush();
    }
}

// --- 1) Allow POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, ["Invalid request method."]);
}

// --- 2) CSRF Protection
if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    respond(false, ["Security check failed. Please try again."]);
}

// --- 3) Collect & sanitize
$fname    = trim($_POST['fname'] ?? '');
$lname    = trim($_POST['lname'] ?? '');
$email    = strtolower(trim($_POST['email'] ?? ''));
$phone    = trim($_POST['phone_number'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';
$terms    = isset($_POST['terms']);

// --- 4) Validation
$errors = [];

if (!$terms) {
    $errors[] = "You must accept the Terms & Conditions.";
}
if (empty($fname) || empty($lname) || empty($email) || empty($password)) {
    $errors[] = "Please fill in all required fields.";
}
if (strlen($fname) > 100 || strlen($lname) > 100) {
    $errors[] = "Name too long (max 100 characters).";
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email address.";
}
if (!preg_match('/^\+?\d{7,15}$/', $phone)) {
    $errors[] = "Invalid phone number format.";
}
if ($password !== $confirm) {
    $errors[] = "Passwords do not match.";
}
if (strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters long.";
}

if (!empty($errors)) {
    respond(false, $errors);
}

// --- 5) Hash password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// --- 6) Unique username generator (keeps your original approach)
function make_unique_username(PDO $pdo, string $base): string {
    $username = $base;
    $i = 1;
    $stmt = $pdo->prepare("
        SELECT username FROM (
            SELECT username FROM users
            UNION
            SELECT username FROM user_registrations
        ) AS combined
        WHERE username = :u
        LIMIT 1
    ");

    while (true) {
        $stmt->execute([':u' => $username]);
        if (!$stmt->fetch()) break;
        $username = $base . $i;
        $i++;
    }
    return $username;
}

$base_username = strtolower(preg_replace('/\s+/', '', $fname . '.' . $lname));

// --- 7) Database ops
try {
    // Check if email exists in users
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        respond(false, ["Email already registered. Please log in or reset your password."]);
    }

    // Check pending registrations (lock the row if DB supports it)
    // This doesn't start an explicit transaction, but using FOR UPDATE requires a transaction in many DBs.
    $stmt = $pdo->prepare("SELECT reg_id, verification_sent_at, expires_at FROM user_registrations WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $pending = $stmt->fetch(PDO::FETCH_ASSOC);

    $token      = bin2hex(random_bytes(16));
    $sent_at    = date('Y-m-d H:i:s');
    $expires_at = date('Y-m-d H:i:s', time() + 3600);

    // Throttle setting: don't allow resending too often (server-side protection)
    $throttleSeconds = 60; // adjust as needed

    if ($pending && strtotime($pending['expires_at']) > time()) {
        // If a verification was sent very recently, block spammy resends
        if (!empty($pending['verification_sent_at'])) {
            $lastSent = strtotime($pending['verification_sent_at']);
            if ($lastSent !== false && (time() - $lastSent) < $throttleSeconds) {
                respond(false, ["A verification email was recently sent. Please check your inbox or wait a minute before requesting another."]);
            }
        }

        // Update the existing pending registration with a new token/timestamps
        $upd = $pdo->prepare("
            UPDATE user_registrations 
            SET verification_token = :token,
                verification_sent_at = :sent_at,
                expires_at = :expires_at,
                password_hash = :password_hash,
                first_name = :fname,
                last_name = :lname,
                phone_number = :phone
            WHERE reg_id = :id
        ");
        $upd->execute([
            ':token'       => $token,
            ':sent_at'     => $sent_at,
            ':expires_at'  => $expires_at,
            ':password_hash'=> $password_hash,
            ':fname'       => $fname,
            ':lname'       => $lname,
            ':phone'       => $phone,
            ':id'          => $pending['reg_id']
        ]);

        // Respond to client immediately (so user isn't blocked by SMTP)
        send_immediate_success_and_continue("/Leilife/public/index.php?page=verify_notice");

        // Continue in background: send email (won't block user's redirect)
        if (!sendVerificationEmail($email, $token)) {
            error_log("Could not send verification email (update path) to $email");
        }

        // End script after background work for this path
        exit;
    }

    if ($pending) {
        // pending exists but is expired — delete it so we can insert a fresh one
        $del = $pdo->prepare("DELETE FROM user_registrations WHERE reg_id = :id");
        $del->execute([':id' => $pending['reg_id']]);
    }

    // Insert new registration
    $username = make_unique_username($pdo, $base_username);

    $ins = $pdo->prepare("
        INSERT INTO user_registrations
        (username, first_name, last_name, email, phone_number, password_hash,
         verification_token, verification_sent_at, expires_at)
        VALUES
        (:username, :fname, :lname, :email, :phone, :password_hash,
         :token, :sent_at, :expires_at)
    ");
    $ins->execute([
        ':username'      => $username,
        ':fname'         => $fname,
        ':lname'         => $lname,
        ':email'         => $email,
        ':phone'         => $phone,
        ':password_hash' => $password_hash,
        ':token'         => $token,
        ':sent_at'       => $sent_at,
        ':expires_at'    => $expires_at
    ]);

    // Respond immediately and continue to send the email in background
    send_immediate_success_and_continue("/Leilife/public/index.php?page=verify_notice");

    if (!sendVerificationEmail($email, $token)) {
        error_log("Signup saved but verification email could not be sent to $email");
    }

    // done
    exit;

} catch (PDOException $e) {
    error_log("Signup DB error: " . $e->getMessage());
    respond(false, ["A database error occurred. Please try again later."]);
} catch (RuntimeException $e) {
    respond(false, [$e->getMessage()]);
} catch (Exception $e) {
    error_log("Unexpected signup error: " . $e->getMessage());
    respond(false, ["Server error. Please try again later."]);
}
