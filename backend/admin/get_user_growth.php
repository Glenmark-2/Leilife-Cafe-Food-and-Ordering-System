<?php
require_once __DIR__ . '/../db_script/db.php';
header('Content-Type: application/json');

$from = $_GET['fromDate'] ?? date('Y-m-01');
$to   = $_GET['toDate'] ?? date('Y-m-t');
$type = $_GET['type'] ?? 'weekly'; // daily | weekly | monthly

try {
    if ($type === 'daily') {
        // DAILY VIEW
        $query = "
            SELECT 
                DATE(created_at) AS label,
                COUNT(*) AS count
            FROM users
            WHERE DATE(created_at) BETWEEN :from AND :to
            GROUP BY DATE(created_at)
            ORDER BY DATE(created_at)
        ";
    } elseif ($type === 'monthly') {
        // MONTHLY VIEW
        $query = "
            SELECT 
                DATE_FORMAT(MIN(created_at), '%Y-%m') AS label,
                COUNT(*) AS count
            FROM users
            WHERE DATE(created_at) BETWEEN :from AND :to
            GROUP BY YEAR(created_at), MONTH(created_at)
            ORDER BY YEAR(created_at), MONTH(created_at)
        ";
    } else {
        // WEEKLY VIEW — Strict-mode-safe + clear “Sep Week 3” style labels
        $query = "
            SELECT 
                CONCAT(
                    DATE_FORMAT(MIN(created_at), '%b'),  -- e.g. Sep, Oct
                    ' Week ',
                    FLOOR((DAYOFMONTH(MIN(created_at)) - 1) / 7) + 1
                ) AS label,
                COUNT(*) AS count
            FROM users
            WHERE DATE(created_at) BETWEEN :from AND :to
            GROUP BY YEAR(created_at), MONTH(created_at), FLOOR((DAYOFMONTH(created_at) - 1) / 7)
            ORDER BY YEAR(created_at), MONTH(created_at), FLOOR((DAYOFMONTH(created_at) - 1) / 7)
        ";
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute([':from' => $from, ':to' => $to]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $rows]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
