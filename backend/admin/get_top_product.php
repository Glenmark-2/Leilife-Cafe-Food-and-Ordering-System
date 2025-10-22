<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db_script/db.php';

$from = $_GET['fromDate'] ?? null;
$to   = $_GET['toDate'] ?? null;

$validDate = fn($d) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);
if (!$from || !$to || !$validDate($from) || !$validDate($to)) {
    echo json_encode(['success' => false, 'error' => 'Invalid date range']);
    exit;
}

try {
    $sql = "
        SELECT 
            p.product_name AS product_name,
            SUM(oi.quantity) AS total_quantity,
            SUM(oi.price * oi.quantity) AS total_revenue
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN products p ON oi.product_id = p.product_id
        WHERE 
            o.status IN ('delivered','picked_up')
            AND o.payment_status = 'paid'
            AND DATE(o.order_date) BETWEEN :from AND :to
        GROUP BY p.product_id, p.product_name
        ORDER BY total_revenue DESC
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':from' => $from, ':to' => $to]);
    $top = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$top) {
        echo json_encode([
            'success' => true,
            'product_name' => 'No sales',
            'total_quantity' => 0,
            'total_revenue' => 0
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'product_name' => $top['product_name'],
        'total_quantity' => (int)$top['total_quantity'],
        'total_revenue' => (float)$top['total_revenue']
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
