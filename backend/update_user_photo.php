<?php
require_once __DIR__ . '/db_script/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION['user_id'];

// Check if file was uploaded
if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
    die("No file uploaded or upload error.");
}

$uploadDir = __DIR__ . '/../public/profile_photos/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// -------------------------
// Delete last photo if exists
// -------------------------
$sql = "SELECT profile_picture FROM users WHERE user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $user_id]);
$oldPhoto = $stmt->fetchColumn();

if ($oldPhoto && file_exists($uploadDir . $oldPhoto)) {
    // Avoid deleting a default placeholder if you have one
    if ($oldPhoto !== 'default.jpg') { 
        unlink($uploadDir . $oldPhoto);
    }
}

// -------------------------
// Handle new upload
// -------------------------
$fileTmp = $_FILES['profile_photo']['tmp_name'];
$fileName = basename($_FILES['profile_photo']['name']);
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png'];

if (!in_array($fileExt, $allowed)) {
    die("Invalid file type. Only JPG and PNG are allowed.");
}

// Rename file to avoid conflicts
$newFileName = "user_" . $user_id . "_" . time() . "." . $fileExt;
$filePath = $uploadDir . $newFileName;
$fileDbPath = $newFileName; // stored in DB

if (!move_uploaded_file($fileTmp, $filePath)) {
    die("Error saving file.");
}

// Save path in database
$sql = "UPDATE users SET profile_picture = :photo WHERE user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':photo' => $fileDbPath,
    ':user_id' => $user_id
]);

// Update session so new photo shows immediately
$_SESSION['profile_picture'] = $fileDbPath;

header("Location: /leilife/public/index.php?page=user-profile");
exit;
?>
