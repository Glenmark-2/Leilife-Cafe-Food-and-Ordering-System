<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
require_once __DIR__ . '/../db_script/db.php';
require_once __DIR__ . '/../db_script/appData.php';

if (empty($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please log in again.']);
    exit;
}

$appData = new AppData($pdo);
$currentAdmin = $appData->getCurrentAdmin();

if (empty($currentAdmin) || empty($currentAdmin['isMainAdmin']) || !$currentAdmin['isMainAdmin']) {
    echo json_encode(['success' => false, 'message' => 'Only main admin can perform this action.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
error_log("DEBUG DELIVERY INPUT: " . print_r($data, true));

if (!isset($data['action']) || $data['action'] !== 'save_delivery_settings') {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    exit;
}

if (empty($data['methods']) || !is_array($data['methods'])) {
    echo json_encode(['success' => false, 'message' => 'No delivery methods provided.']);
    exit;
}

foreach ($data['methods'] as $k => $m) {
    $data['methods'][$k]['id'] = (int)$m['id'];
    $data['methods'][$k]['status'] = ((int)$m['status'] === 1) ? 'enabled' : 'disabled';
}

// 🧩 Validate at least one enabled
$enabledCount = count(array_filter($data['methods'], fn($m) => $m['status'] === 'enabled'));
if ($enabledCount < 1) {
    echo json_encode(['success' => false, 'message' => 'At least one delivery method must be enabled.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE delivery_options SET status = :status WHERE delivery_id = :id");
    $checkStmt = $pdo->prepare("SELECT status FROM delivery_options WHERE delivery_id = :id");
    $rowCount = 0;

    foreach ($data['methods'] as $method) {
        $id = (int)$method['id'];
        $status = $method['status']; 

        $checkStmt->execute([':id' => $id]);
        $currentStatus = $checkStmt->fetchColumn();

        error_log(" ID {$id} before: {$currentStatus}, after: {$status}");

        $success = $stmt->execute([
            ':status' => $status,
            ':id' => $id
        ]);

        if (!$success) {
            error_log("Failed to update ID {$id} to {$status}");
        } else {
            $affected = $stmt->rowCount();
            error_log("Updated ID {$id} to {$status}, affected: $affected");
            $rowCount += $affected;
        }
    }

    echo json_encode([
        'success' => true,
        'message' => $rowCount > 0
            ? 'Delivery settings updated successfully.'
            : 'No changes detected — already up to date.'
    ]);
} catch (Exception $e) {
    error_log("❌ Exception during update: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
