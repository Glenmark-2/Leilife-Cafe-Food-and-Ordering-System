<?php
require_once __DIR__ . '/../db_script/db.php';

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staffId = $_POST['id'] ?? null;

    if (!$staffId) {
        echo json_encode(["success" => false, "message" => "Missing staff ID"]);
        exit;
    }

    try {
        // Delete staff from database
        $stmt = $pdo->prepare("DELETE FROM staff_roles WHERE staff_id = ?");
        $success = $stmt->execute([$staffId]);

        if ($success) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to delete staff"]);
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}
