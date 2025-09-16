<?php
session_start();
require_once __DIR__ . '/db_script/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $type = "mail";
    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message']);
    $user_id = $_SESSION['user_id'] ?? null;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email address.");
    }

    if (strlen($name) > 100 || strlen($subject) > 100) {
        die("❌ Input too long.");
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

        header("Location: /leilife/public/index.php?page=home");
        exit;
    } catch (PDOException $e) {
        error_log("DB Error: " . $e->getMessage());
        echo "⚠️ Something went wrong. Please try again later.";
    }
}
