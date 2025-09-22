<?php
require_once __DIR__ . '/../db_script/db.php';

$response = ["success" => false, "message" => ""];

try {
    if (!isset($_POST['id'])) {
        throw new Exception("Missing staff ID");
    }

    $id = $_POST['id'];
    $name = $_POST['name'] ?? '';
    $role = $_POST['role'] ?? '';
    $shift = $_POST['shift'] ?? '';
    $status = $_POST['status'] ?? '';

    // Handle file upload if provided
    $photoName = null;
    if (!empty($_FILES['photo']['name'])) {
        $photoName = time() . "_" . basename($_FILES['photo']['name']);
        $target = __DIR__ . "/../../public/staffs/" . $photoName;
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
            throw new Exception("Failed to upload photo");
        }
    }

    // Update query
    $sql = "UPDATE staff_roles 
            SET staff_name = :name,
                staff_role = :role,
                shift = :shift,
                status = :status"
            . ($photoName ? ", staff_image = :photo" : "") . 
            " WHERE staff_id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":name", $name);
    $stmt->bindParam(":role", $role);
    $stmt->bindParam(":shift", $shift);
    $stmt->bindParam(":status", $status);
    $stmt->bindParam(":id", $id);
    if ($photoName) $stmt->bindParam(":photo", $photoName);

    if ($stmt->execute()) {
        $response["success"] = true;
    } else {
        throw new Exception("Database update failed");
    }
    if ($stmt->rowCount() > 0) {
        $response["success"] = true;
        $response["message"] = "Staff updated successfully!";
    } else {
        //  No rows affected (means no changes were made)
        $response["success"] = false;
        $response["message"] = "No changes were made.";
    }

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
