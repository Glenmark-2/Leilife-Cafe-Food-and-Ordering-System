<?php
// File: /backend/helpers/log_helper.php
require_once __DIR__ . '/../db_script/db.php';

function logAction($user, $action, $target, $status) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO audit_logs (user, action, target, status, datetime)
            VALUES (:user, :action, :target, :status, NOW())
        ");
        $stmt->execute([
            ':user' => $user,
            ':action' => $action,
            ':target' => $target,
            ':status' => $status,
        ]);
    } catch (Exception $e) {
        error_log("Audit log failed: " . $e->getMessage());
    }
}
?>
