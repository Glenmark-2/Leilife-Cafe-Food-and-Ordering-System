<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db_script/db.php';

$stmt = $pdo->query("SELECT * FROM staff_roles ORDER BY staff_id DESC");
$staff = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($staff);