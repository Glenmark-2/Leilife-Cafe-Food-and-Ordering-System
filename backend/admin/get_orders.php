<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db_script/db.php';

try {
    $sort = $_GET['sort'] ?? 'order_date';
    $allowed = ['order_date', 'status', 'total'];
    if (!in_array($sort, $allowed)) $sort = 'order_date';

    // ✅ New parameter: view (active | completed)
    $view = $_GET['view'] ?? 'active';

    // ✅ Build base condition (no default CURDATE filter yet)
    $where = "1=1";

    if ($view === 'active') {
        // Show ALL ongoing orders (no date restriction)
        $where .= " AND o.status IN ('pending', 'preparing', 'ready_for_delivery')";
    } elseif ($view === 'completed') {
        // Show only today's finished or cancelled orders
        $where .= " AND o.status IN ('delivered', 'cancelled') 
                    AND DATE(o.order_date) = CURDATE()";
    } else {
        // fallback: show everything
        $where .= "";
    }

    // ✅ Dynamic query
    $stmt = $pdo->query("
        SELECT 
            o.order_id,
            o.order_number,
            o.user_id,
            u.first_name AS customer_name,
            o.order_date,
            o.status,
            o.total,
            o.payment_method,
            o.payment_status,
            COUNT(oi.order_item_id) AS items_count
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        WHERE $where
        GROUP BY o.order_id
        ORDER BY o.$sort " . ($view === 'active' ? "ASC" : "DESC")
    );

    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "orders" => $orders]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
