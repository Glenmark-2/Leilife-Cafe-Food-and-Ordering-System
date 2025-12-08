<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db_script/db.php';

// ✅ Normalize inputs
$name   = isset($_POST['name'])   ? ucwords(strtolower(trim($_POST['name']))) : null;
$role   = isset($_POST['role'])   ? trim($_POST['role'])   : null;
$shift  = isset($_POST['shift'])  ? trim($_POST['shift'])  : null;
$status = isset($_POST['status']) ? trim($_POST['status']) : "active";

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

    // ✅ Sanitize and trim filename
    $cleanFileName = preg_replace("/[^A-Za-z0-9.\-_]/", "_", trim($_FILES['photo']['name']));
    $imageName = time() . "_" . $cleanFileName;
    $targetFile = $uploadDir . $imageName;

    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
        echo json_encode(["success" => false, "message" => "Image upload failed"]);
        exit;
    }
}

try {
    // ✅ Case-insensitive & trimmed duplicate name check
    $checkName = $pdo->prepare("SELECT COUNT(*) FROM staff_roles WHERE LOWER(TRIM(staff_name)) = LOWER(TRIM(:name))");
    $checkName->execute([":name" => $name]);
    if ($checkName->fetchColumn() > 0) {
        echo json_encode(["success" => false, "message" => "A staff member with this name already exists."]);
        exit;
    }

    // ✅ Case-insensitive & trimmed duplicate image check (only if not default image)
    if ($imageName !== "about us.png") {
        $checkImage = $pdo->prepare("SELECT COUNT(*) FROM staff_roles WHERE LOWER(TRIM(staff_image)) = LOWER(TRIM(:img))");
        $checkImage->execute([":img" => $imageName]);
        if ($checkImage->fetchColumn() > 0) {
            echo json_encode(["success" => false, "message" => "A staff member with this image already exists."]);
            exit;
        }
    }

    // ✅ Insert normalized values into DB
    $stmt = $pdo->prepare("INSERT INTO staff_roles (staff_name, staff_role, shift, status, staff_image) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $role, $shift, $status, $imageName]);

    echo json_encode([
        "success" => true,
        "staff_name"  => $name,
        "staff_image" => $imageName,
        "inserted_id" => $pdo->lastInsertId()
    ]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
