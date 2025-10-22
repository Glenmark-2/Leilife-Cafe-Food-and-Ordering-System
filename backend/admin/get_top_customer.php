<?php
require_once __DIR__ . '/../db_script/db.php'; // adjust if needed

header('Content-Type: application/json');

try {
    $fromDate = $_GET['fromDate'] ?? null;
    $toDate = $_GET['toDate'] ?? null;

    $query = "
        SELECT 
            CONCAT(u.first_name, ' ', u.last_name) AS customer_name,
            COUNT(o.order_id) AS total_orders,
            SUM(o.total) AS total_spent
        FROM orders o
        JOIN users u ON o.user_id = u.user_id
        WHERE o.status = 'delivered'
    ";

    $params = [];

    if ($fromDate && $toDate) {
        $query .= " AND DATE(o.order_date) BETWEEN ? AND ?";
        $params = [$fromDate, $toDate];
    } elseif ($fromDate) {
        $query .= " AND DATE(o.order_date) >= ?";
        $params = [$fromDate];
    } elseif ($toDate) {
        $query .= " AND DATE(o.order_date) <= ?";
        $params = [$toDate];
    }

    $query .= " GROUP BY u.user_id ORDER BY total_spent DESC LIMIT 1";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode([
            "success" => true,
            "customer_name" => $result['customer_name'],
            "total_orders" => (int) $result['total_orders'],
            "total_spent" => (float) $result['total_spent']
        ]);
    } else {
        echo json_encode(["success" => true, "customer_name" => "No Data", "total_orders" => 0, "total_spent" => 0]);
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
