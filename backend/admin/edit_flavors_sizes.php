<?php
require_once __DIR__ . '/../db_script/db.php';

header('Content-Type: application/json');

function respond($success, $message = '', $data = []) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.');
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) respond(false, 'Invalid JSON.');

$flavors = $input['flavors'] ?? [];
$sizes = $input['sizes'] ?? [];

try {
    $pdo->beginTransaction();

    // --- Flavors ---
    foreach ($flavors as $flavor) {
        $id = (int)($flavor['id'] ?? 0);
        $name = trim($flavor['name'] ?? '');
        $status = $flavor['status'] === 'Unavailable' ? 'Unavailable' : 'Available'; // sanitize

        if ($name === '') continue;

        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE product_flavors SET flavor_name = ?, status = ? WHERE flavor_id = ?");
            $stmt->execute([$name, $status, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO product_flavors (flavor_name, status) VALUES (?, ?)");
            $stmt->execute([$name, $status]);
        }
    }

    // --- Sizes ---
    foreach ($sizes as $size) {
        $id = (int)($size['id'] ?? 0);
        $name = trim($size['name'] ?? '');
        $status = $size['status'] === 'Unavailable' ? 'Unavailable' : 'Available';

        if ($name === '') continue;

        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE drink_size SET size_name = ?, status = ? WHERE size_id = ?");
            $stmt->execute([$name, $status, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO drink_size (size_name, status) VALUES (?, ?)");
            $stmt->execute([$name, $status]);
        }
    }

    $pdo->commit();
    respond(true, 'Flavors and sizes updated successfully.');
} catch (Exception $e) {
    $pdo->rollBack();
    respond(false, 'Error: ' . $e->getMessage());
}
