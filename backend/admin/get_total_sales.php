<?php
header('Content-Type: application/json; charset=utf-8');

// adjust path if your db.php location is different
require_once __DIR__ . '/../db_script/db.php'; // <-- should set $pdo (PDO instance)

// Validate and sanitize input
$from = $_GET['fromDate'] ?? null;
$to   = $_GET['toDate']   ?? null;

// simple validation: if dates provided, ensure format YYYY-MM-DD
$validDate = function($d){
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);
};

// Default: entire range if no valid dates provided
if ($from && !$validDate($from)) { http_response_code(400); echo json_encode(['error' => 'invalid fromDate']); exit; }
if ($to   && !$validDate($to))   { http_response_code(400); echo json_encode(['error' => 'invalid toDate']); exit; }

// If only from provided, set to today; if only to provided, set from to earliest
if (!$from && $to) {
    $from = '1970-01-01';
}
if ($from && !$to) {
    $to = date('Y-m-d');
}
if (!$from && !$to) {
    // default range: current month
    $from = date('Y-m-01');
    $to   = date('Y-m-d');
}

try {
    // Calculate net total: sum of order total for completed & paid orders minus succeeded refunds
    $sql = "
    SELECT 
      COALESCE(SUM(o.total), 0) AS orders_total,
      COALESCE(SUM(r.amount), 0) AS refunds_total,
      (COALESCE(SUM(o.total), 0) - COALESCE(SUM(r.amount), 0)) AS net_total
    FROM orders o
    LEFT JOIN refunds r
      ON o.order_id = r.order_id AND r.status = 'succeeded'
    WHERE 
      o.status IN ('delivered', 'picked_up')
      AND o.payment_status = 'paid'
      AND DATE(o.order_date) BETWEEN :from AND :to
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':from' => $from, ':to' => $to]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // ensure numeric
    $net = $row['net_total'] !== null ? (float)$row['net_total'] : 0.00;

    echo json_encode([
        'success' => true,
        'from' => $from,
        'to' => $to,
        'orders_total' => (float)$row['orders_total'],
        'refunds_total' => (float)$row['refunds_total'],
        'total_sales' => $net
    ], JSON_NUMERIC_CHECK);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error', 'details' => $e->getMessage()]);
}
