<?php
require_once __DIR__ . '/../db_script/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $fromDate = $_GET['fromDate'] ?? null;
    $toDate   = $_GET['toDate'] ?? null;

    // 🔹 Base query: total revenue grouped by main category
    $query = "
        SELECT 
            mc.main_category_name AS category,
            SUM(oi.price * oi.quantity) AS total_revenue
        FROM order_items oi
        INNER JOIN orders o ON oi.order_id = o.order_id
        INNER JOIN products p ON oi.product_id = p.product_id
        INNER JOIN categories c ON p.category_id = c.category_id
        INNER JOIN (
            SELECT DISTINCT main_category_id, main_category_name FROM categories
        ) mc ON c.main_category_id = mc.main_category_id
        WHERE o.payment_status IN ('paid', 'completed')
    ";

    // 🔹 Add date filtering
    if ($fromDate && $toDate) {
        $query .= " AND o.order_date BETWEEN :fromDate AND :toDate";
    }

    $query .= " GROUP BY mc.main_category_name ORDER BY total_revenue DESC";

    $stmt = $pdo->prepare($query);
    if ($fromDate && $toDate) {
        $stmt->bindParam(':fromDate', $fromDate);
        $stmt->bindParam(':toDate', $toDate);
    }
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 🔹 Build data array for Chart.js
    $data = [];
    foreach ($rows as $row) {
        $cat = ucfirst(trim($row['category']));
        $data[$cat] = [
            'revenue' => [(float)$row['total_revenue']]
        ];
    }

    // 🔹 Return consistent response
    echo json_encode([
        'success' => true,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
