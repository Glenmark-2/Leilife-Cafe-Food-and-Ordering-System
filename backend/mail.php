<?php
session_start();
require_once __DIR__ . '/db_script/db.php';

header('Content-Type: application/json'); // Important!

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $type = "mail";
    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message']);
    $user_id = $_SESSION['user_id'] ?? null;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
        exit;
    }

    if (strlen($name) > 100 || strlen($subject) > 100) {
        echo json_encode(['success' => false, 'message' => 'Input too long.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO inbox (type, email, name, subject, user_id, message)
            VALUES (:type, :email, :name, :subject, :user_id, :message)
        ");

        $stmt->execute([
            ':type'    => $type,
            ':email'   => $email,
            ':name'    => $name,
            ':subject' => $subject,
            ':user_id' => $user_id,
            ':message' => $message
        ]);

        echo json_encode(['success' => true, 'message' => 'Your message has been sent!']);
        exit;
    } catch (PDOException $e) {
        error_log("DB Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again later.']);
        exit;
    }
}
