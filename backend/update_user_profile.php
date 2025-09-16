<?php
require_once __DIR__ . '/db_script/db.php';
session_start();

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $phone      = trim($_POST['phone_number'] ?? '');
    $user_id    = $_SESSION['user_id'] ?? null;

    if (!$user_id) {
        echo json_encode(["success" => false, "error" => "User not logged in"]);
        exit;
    }

    if ($first_name === '' || $last_name === '') {
        echo json_encode(["success" => false, "error" => "First and Last name are required"]);
        exit;
    }

    if ($phone !== '' && !preg_match('/^(\+63\d{9}|09\d{9})$/', $phone)) {
        echo json_encode(["success" => false, "error" => "Invalid phone number"]);
        exit;
    }


    $sql = "UPDATE users 
            SET first_name = :first_name, 
                last_name = :last_name, 
                phone_number = :phone
            WHERE user_id = :user_id";

    $stmt = $pdo->prepare($sql);
    $ok = $stmt->execute([
        ':first_name' => $first_name,
        ':last_name'  => $last_name,
        ':phone'      => $phone,
        ':user_id'    => $user_id
    ]);

    echo json_encode(
        $ok
            ? ["success" => true, "message" => "Profile updated successfully"]
            : ["success" => false, "error" => "Database error"]
    );
    exit;
}

echo json_encode(["success" => false, "error" => "Invalid request"]);
