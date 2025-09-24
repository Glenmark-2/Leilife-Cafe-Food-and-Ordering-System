<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db_script/db.php';

$response = ["success" => false, "message" => ""];

try {
    if (!isset($_POST['staff_id'])) {
        throw new Exception("Missing staff ID");
    }

    $staff_id = (int) $_POST['staff_id'];

    // Get current status and role
    $stmt = $pdo->prepare("SELECT staff_name, staff_role, is_archive FROM staff_roles WHERE staff_id = ?");
    $stmt->execute([$staff_id]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$current) {
        throw new Exception("Staff not found");
    }

    $new_status = $current['is_archive'] == 1 ? 0 : 1;

    $updateStaff = $pdo->prepare("UPDATE staff_roles SET is_archive = ? WHERE staff_id = ?");
    $updateStaff->execute([$new_status, $staff_id]);

    if (strtolower($current['staff_role']) === 'admin') {
        $updateAdmin = $pdo->prepare("UPDATE admin_account SET is_active = ? WHERE full_name = ?");
        $updateAdmin->execute([$new_status == 1 ? 0 : 1, $current['staff_name']]);
    }

    $response['success'] = true;
    $response['message'] = $new_status === 1 ? "Staff archived" : "Staff restored";
    $response['new_status'] = $new_status;

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
