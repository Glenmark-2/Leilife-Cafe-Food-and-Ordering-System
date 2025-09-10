<?php
require_once __DIR__ . '/../db_script/db.php';
header('Content-Type: application/json');

// Get input data
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['id'])) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE staff_roles
        SET staff_name = :name,
            staff_role = :role,
            shift = :shift,
            status = :status
        WHERE staff_id = :id
    ");

    $stmt->execute([
        ':name'   => $data['name'],
        ':role'   => $data['role'],
        ':shift'  => $data['shift'],
        ':status' => $data['status'],
        ':id'     => $data['id']
    ]);

    // check if any row was updated
    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "No changes made."]);
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
