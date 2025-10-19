<?php
require_once __DIR__ . '/../db_script/db.php';

$response = ["success" => false, "message" => ""];

try {
    if (!isset($_POST['id'])) {
        throw new Exception("Missing staff ID");
    }

    $id     = $_POST['id'];
    $name   = $_POST['name']   ?? '';
    $role   = $_POST['role']   ?? '';
    $shift  = $_POST['shift']  ?? '';
    $status = $_POST['status'] ?? '';

    // Handle file upload if provided
    $photoName = null;
    if (!empty($_FILES['photo']['name'])) {
        $uploadDir = __DIR__ . "/../../public/staffs/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $cleanFileName = preg_replace("/[^A-Za-z0-9.\-_]/", "_", trim($_FILES['photo']['name']));
        $photoName = time() . "_" . $cleanFileName;
        $target = $uploadDir . $photoName;

        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
            throw new Exception("Failed to upload photo");
        }
    }

    // --- Update staff_roles ---
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
    $stmt->execute();

    // --- Sync with admin_accounts or driver_accounts if needed ---
    if (strtolower($role) === "admin") {
        $stmtAdmin = $pdo->prepare("UPDATE admin_accounts 
                                    SET full_name = :name
                                    WHERE full_name = (
                                        SELECT staff_name FROM staff_roles WHERE staff_id = :id
                                    )");
        $stmtAdmin->execute([":name" => $name, ":id" => $id]);
    } elseif (strtolower($role) === "driver") {
        $stmtDriver = $pdo->prepare("UPDATE driver_accounts 
                                     SET full_name = :name
                                     WHERE full_name = (
                                        SELECT staff_name FROM staff_roles WHERE staff_id = :id
                                     )");
        $stmtDriver->execute([":name" => $name, ":id" => $id]);
    }

    if ($stmt->rowCount() > 0) {
        $response["success"] = true;
        $response["message"] = "Staff updated successfully!";
    } else {
        $response["success"] = true; // still success, even if no rows affected
        $response["message"] = "No changes were made.";
    }

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
