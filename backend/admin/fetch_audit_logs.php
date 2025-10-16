<?php
require_once __DIR__ . '/../db_script/db.php'; // ✅ fixed relative path
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $stmt = $pdo->query("SELECT user, action, target, status, datetime FROM audit_logs ORDER BY datetime DESC");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
