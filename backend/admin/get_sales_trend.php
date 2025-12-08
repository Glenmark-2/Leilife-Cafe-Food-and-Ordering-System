<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db_script/db.php';

try {
  $from = $_GET['fromDate'] ?? null;
  $to   = $_GET['toDate'] ?? null;
  $type = $_GET['type'] ?? 'weekly';

  $groupBy = match($type) {
    'daily' => "DATE(order_date)",
    'monthly' => "DATE_FORMAT(order_date, '%Y-%m')",
    default => "YEARWEEK(order_date, 1)"
  };

  $sql = "
    SELECT 
      $groupBy AS label,
      SUM(total) AS total_sales
    FROM orders
    WHERE status = 'completed'
      " . ($from ? "AND DATE(order_date) >= ?" : "") . "
      " . ($to ? "AND DATE(order_date) <= ?" : "") . "
    GROUP BY label
    ORDER BY label ASC
  ";

  $stmt = $pdo->prepare($sql);
  $params = [];
  if ($from) $params[] = $from;
  if ($to)   $params[] = $to;
  $stmt->execute($params);

  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode(['success' => true, 'data' => $data]);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
