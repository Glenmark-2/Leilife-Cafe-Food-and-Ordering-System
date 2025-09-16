<?php
require_once __DIR__ . '/db_script/db.php';
session_start();

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? null;

    if (!$user_id) {
        echo json_encode(["success" => false, "error" => "Unauthorized"]);
        exit;
    }

    $street_address = trim($_POST['street_address'] ?? '');
    $barangay = trim($_POST['barangay'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $region = trim($_POST['region'] ?? '');

    if (!$street_address || !$barangay || !$city || !$province || !$region) {
        echo json_encode(["success" => false, "error" => "All fields are required"]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT user_id FROM addresses WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $sql = "UPDATE addresses 
                SET street_address = :street, barangay = :barangay, city = :city,
                    province = :province, region = :region
                WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $ok = $stmt->execute([
            ':user_id' => $user_id,
            ':street' => $street_address,
            ':barangay' => $barangay,
            ':city' => $city,
            ':province' => $province,
            ':region' => $region
        ]);
    } else {
        $sql = "INSERT INTO addresses 
                (user_id, street_address, barangay, city, province, region, payment_method, delivery_option)
                VALUES (:user_id, :street, :barangay, :city, :province, :region, 'cash_on_delivery', 'delivery')";
        $stmt = $pdo->prepare($sql);
        $ok = $stmt->execute([
            ':user_id' => $user_id,
            ':street' => $street_address,
            ':barangay' => $barangay,
            ':city' => $city,
            ':province' => $province,
            ':region' => $region
        ]);
    }

    echo json_encode($ok 
        ? ["success" => true, "message" => "Address updated successfully"] 
        : ["success" => false, "error" => "Database error"]);
    exit;
}

echo json_encode(["success" => false, "error" => "Invalid request"]);
