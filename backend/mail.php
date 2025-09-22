<?php
session_start();
require_once __DIR__ . '/db_script/db.php';

header('Content-Type: application/json'); // Important!

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $type    = "mail";
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

    // -------------------------------
    // Run Python Sentiment Analyzer
    // -------------------------------
    $tempFile = __DIR__ . "/ML/temp_input.txt";
    file_put_contents($tempFile, $message);

    $pythonExe  = "C:\\Users\\glenm\\AppData\\Local\\Programs\\Python\\Python312\\python.exe";
    $scriptPath = __DIR__ . "\\ML\\sentiment_analyzer.py";

    $command = "\"$pythonExe\" \"$scriptPath\" \"$tempFile\"";
    $output  = shell_exec($command . " 2>&1");

    $result = json_decode($output, true);

    $sentiment = $result['label'] ?? 'UNKNOWN';
    $score     = $result['score'] ?? 0;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO inbox (type, email, name, subject, user_id, message, sentiment, sentiment_score)
            VALUES (:type, :email, :name, :subject, :user_id, :message, :sentiment, :sentiment_score)
        ");

        $stmt->execute([
            ':type'            => $type,
            ':email'           => $email,
            ':name'            => $name,
            ':subject'         => $subject,
            ':user_id'         => $user_id,
            ':message'         => $message,
            ':sentiment'       => $sentiment,
            ':sentiment_score' => $score
        ]);

        echo json_encode([
            'success'   => true,
            'message'   => 'Your message has been sent!',
            'sentiment' => $sentiment,
            'score'     => $score
        ]);
        exit;
    } catch (PDOException $e) {
        error_log("DB Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again later.']);
        exit;
    }
}
