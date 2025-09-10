<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db_script/db.php';

$name   = $_POST['name']   ?? null;
$role   = $_POST['role']   ?? null;
$shift  = $_POST['shift']  ?? null;
$status = $_POST['status'] ?? "Available";

if (!$name || !$role || !$shift) {
    echo json_encode(["success" => false, "message" => "Missing fields"]);
    exit;
}

$imageName = "about us.png"; // default image
if (!empty($_FILES['photo']['name'])) {
    $uploadDir = __DIR__ . '/../../public/staffs/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $imageName = time() . "_" . preg_replace("/[^A-Za-z0-9.\-_]/", "_", $_FILES['photo']['name']);
    $targetFile = $uploadDir . $imageName;

    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
        echo json_encode(["success" => false, "message" => "Image upload failed"]);
        exit;
    }
}

try {
    $stmt = $pdo->prepare("INSERT INTO staff_roles (staff_name, staff_role, shift, status, staff_image) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $role, $shift, $status, $imageName]);

    echo json_encode([
        "success" => true,
        "staff_image" => $imageName,
        "inserted_id" => $pdo->lastInsertId()
    ]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
