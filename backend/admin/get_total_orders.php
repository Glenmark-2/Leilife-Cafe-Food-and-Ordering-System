<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db_script/db.php'; // adjust if needed

$from = $_GET['fromDate'] ?? null;
$to   = $_GET['toDate'] ?? null;

$validDate = function($d){
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);
};

if ($from && !$validDate($from)) { http_response_code(400); echo json_encode(['error' => 'invalid fromDate']); exit; }
if ($to && !$validDate($to)) { http_response_code(400); echo json_encode(['error' => 'invalid toDate']); exit; }

if (!$from && !$to) {
    // default current month
    $from = date('Y-m-01');
    $to   = date('Y-m-d');
} elseif (!$from && $to) {
    $from = '1970-01-01';
} elseif ($from && !$to) {
    $to = date('Y-m-d');
}

try {
    $sql = "
        SELECT COUNT(*) AS total_orders
        FROM orders
        WHERE 
            status IN ('delivered', 'picked_up')
            AND payment_status = 'paid'
            AND DATE(order_date) BETWEEN :from AND :to
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':from' => $from, ':to' => $to]);
    $totalOrders = $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'from' => $from,
        'to' => $to,
        'total_orders' => (int)$totalOrders
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error',
        'details' => $e->getMessage()
    ]);
}
