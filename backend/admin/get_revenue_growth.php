<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db_script/db.php';

$from = $_GET['fromDate'] ?? null;
$to   = $_GET['toDate'] ?? null;

// date validation
$validDate = fn($d) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);

if (!$from || !$to || !$validDate($from) || !$validDate($to)) {
    echo json_encode(['success'=>false,'error'=>'Invalid date range']);
    exit;
}

try {
    // compute previous period range
    $fromDate = new DateTime($from);
    $toDate = new DateTime($to);
    $interval = $fromDate->diff($toDate)->days + 1;

    $prevTo = (clone $fromDate)->modify('-1 day');
    $prevFrom = (clone $prevTo)->modify("-{$interval} day")->modify('+1 day');

    // current period total
    $sql = "
        SELECT COALESCE(SUM(total),0) AS total
        FROM orders
        WHERE status IN ('delivered','picked_up')
          AND payment_status='paid'
          AND DATE(order_date) BETWEEN :from AND :to
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':from'=>$from, ':to'=>$to]);
    $currentTotal = (float)$stmt->fetchColumn();

    // previous period total
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':from'=>$prevFrom->format('Y-m-d'), ':to'=>$prevTo->format('Y-m-d')]);
    $prevTotal = (float)$stmt->fetchColumn();

    $growth = $prevTotal > 0 ? (($currentTotal - $prevTotal) / $prevTotal) * 100 : 0;

    echo json_encode([
        'success'=>true,
        'from'=>$from,
        'to'=>$to,
        'previous_from'=>$prevFrom->format('Y-m-d'),
        'previous_to'=>$prevTo->format('Y-m-d'),
        'current_total'=>$currentTotal,
        'previous_total'=>$prevTotal,
        'growth_percent'=>round($growth, 2)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
