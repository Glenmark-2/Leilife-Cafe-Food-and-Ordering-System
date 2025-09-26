<?php 
session_start();
require_once __DIR__ . "/../db_script/db.php";

header("Content-Type: application/json");

if (!isset($_SESSION['pending_account'])) {
    echo json_encode(["success" => false, "message" => "No pending account."]);
    exit;
}

$pending = $_SESSION['pending_account'];
$userOtp = trim($_POST['otp'] ?? '');

// ✅ OTP check
if ($userOtp !== $pending['otp'] || time() > $pending['expires']) {
    echo json_encode(["success" => false, "message" => "Expired OTP"]);
    exit;
}

try {
    // ✅ Normalize inputs
    $name     = isset($pending['name'])     ? ucwords(strtolower(trim($pending['name']))) : null;
    $role     = isset($pending['role'])     ? trim($pending['role']) : null;
    $shift    = isset($pending['shift'])    ? trim($pending['shift']) : null;
    $status   = "Active";
    $username = isset($pending['username']) ? trim($pending['username']) : null;
    $email    = isset($pending['email'])    ? trim($pending['email']) : null;
    $password = isset($pending['password']) ? $pending['password'] : null; // already hashed in request step

    if (!$name || !$role || !$shift || !$username || !$email || !$password) {
        echo json_encode(["success" => false, "message" => "Missing required fields"]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Invalid email format"]);
        exit;
    }

    // ✅ Check duplicate staff name
    $checkName = $pdo->prepare("SELECT COUNT(*) FROM staff_roles WHERE LOWER(TRIM(staff_name)) = LOWER(TRIM(:name))");
    $checkName->execute([":name" => $name]);
    if ($checkName->fetchColumn() > 0) {
        echo json_encode(["success" => false, "message" => "A staff member with this name already exists."]);
        exit;
    }

    // ✅ Handle photo
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
    } elseif (!empty($pending['photo'])) {
        $imageName = $pending['photo'];
    }

    // ✅ Insert into correct accounts table
    $roleLower = strtolower($role);
    if ($roleLower === "admin") {
        // Check duplicate username/email
        $check = $pdo->prepare("SELECT COUNT(*) FROM admin_accounts WHERE LOWER(TRIM(username)) = LOWER(TRIM(:username)) OR LOWER(TRIM(email)) = LOWER(TRIM(:email))");
        $check->execute([":username" => $username, ":email" => $email]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(["success" => false, "message" => "Username or email already exists in Admin accounts."]);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO admin_accounts (username, email, password, full_name) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $password, $name]);

    } elseif ($roleLower === "driver") {
        // Check duplicate username/email
        $check = $pdo->prepare("SELECT COUNT(*) FROM driver_accounts WHERE LOWER(TRIM(username)) = LOWER(TRIM(:username)) OR LOWER(TRIM(email)) = LOWER(TRIM(:email))");
        $check->execute([":username" => $username, ":email" => $email]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(["success" => false, "message" => "Username or email already exists in Driver accounts."]);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO driver_accounts (username, email, password, full_name) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $password, $name]);

    } else {
        echo json_encode(["success" => false, "message" => "Invalid role"]);
        exit;
    }

    // ✅ Insert into staff_roles
    $stmt = $pdo->prepare("INSERT INTO staff_roles (staff_name, staff_role, shift, status, staff_image) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $role, $shift, $status, $imageName]);
    $staffId = $pdo->lastInsertId();

    unset($_SESSION['pending_account']);

    echo json_encode([
        "success" => true,
        "message" => "Account created successfully!",
        "staff_name" => $name,
        "staff_image" => $imageName,
        "username" => $username,
        "email" => $email,
        "inserted_staff_id" => $staffId
    ]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
