<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db_script/db.php';

$name     = isset($_POST['name'])     ? ucwords(strtolower(trim($_POST['name']))) : null;
$role     = isset($_POST['role'])     ? trim($_POST['role']) : null;
$shift    = isset($_POST['shift'])    ? trim($_POST['shift']) : null;
$status   = isset($_POST['status'])   ? trim($_POST['status']) : "Active";
$username = isset($_POST['username']) ? trim($_POST['username']) : null;
$email    = isset($_POST['email'])    ? trim($_POST['email']) : null;
$password = isset($_POST['password']) ? $_POST['password'] : null;

if (!$name || !$role || !$shift || !$username || !$email || !$password) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$imageName = "uploadImg.jpg";
if (!empty($_FILES['photo']['name'])) {
    $uploadDir = __DIR__ . '/../../public/staffs/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $cleanFileName = preg_replace("/[^A-Za-z0-9.\-_]/", "_", trim($_FILES['photo']['name']));
    $imageName = time() . "_" . $cleanFileName;
    $targetFile = $uploadDir . $imageName;

    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
        echo json_encode(["success" => false, "message" => "Image upload failed"]);
        exit;
    }
}

try {
    // Check for duplicate staff name
    $checkName = $pdo->prepare("SELECT COUNT(*) FROM staff_roles WHERE LOWER(TRIM(staff_name)) = LOWER(TRIM(:name))");
    $checkName->execute([":name" => $name]);
    if ($checkName->fetchColumn() > 0) {
        echo json_encode(["success" => false, "message" => "A staff member with this name already exists."]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Invalid email format"]);
        exit;
    }

    // Check for duplicate username/email in admin_account
    $checkUsername = $pdo->prepare("SELECT COUNT(*) FROM admin_account WHERE LOWER(TRIM(username)) = LOWER(TRIM(:username)) OR LOWER(TRIM(email)) = LOWER(TRIM(:email))");
    $checkUsername->execute([":username" => $username, ":email" => $email]);
    if ($checkUsername->fetchColumn() > 0) {
        echo json_encode(["success" => false, "message" => "Username or email already exists."]);
        exit;
    }

    $is_admin = strtolower($role) === "admin" ? 1 : 0;

    $stmt = $pdo->prepare("INSERT INTO staff_roles (staff_name, staff_role, shift, status, staff_image, is_admin) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $role, $shift, $status, $imageName, $is_admin]);
    $staffId = $pdo->lastInsertId();

    $stmtAdmin = $pdo->prepare("INSERT INTO admin_account (username, email, password, full_name) VALUES (?, ?, ?, ?)");
    $stmtAdmin->execute([$username, $email, $hashedPassword, $name]);

    echo json_encode([
        "success" => true,
        "staff_name" => $name,
        "staff_image" => $imageName,
        "username" => $username,
        "email" => $email,
        "inserted_staff_id" => $staffId
    ]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
