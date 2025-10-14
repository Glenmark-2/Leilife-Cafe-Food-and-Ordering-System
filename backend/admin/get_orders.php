<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db_script/db.php';

try {
    $sort = $_GET['sort'] ?? 'order_date';
    $allowed = ['order_date', 'status', 'total', 'pickup', 'home_delivery'];
    if (!in_array($sort, $allowed)) $sort = 'order_date';

    $view = $_GET['view'] ?? 'active';
    $orderNumber = $_GET['order_number'] ?? '';

    // Base WHERE
    $where = "1=1";
    $params = [];

    if ($view === 'active') {
        $where .= " AND o.status IN ('pending', 'preparing', 'ready_for_delivery')";
    } elseif ($view === 'completed') {
        $where .= " AND o.status IN ('delivered', 'cancelled') 
                    AND DATE(o.order_date) = CURDATE()";
    }

    if ($orderNumber !== '') {
        $where .= " AND o.order_number LIKE :order_number";
        $params['order_number'] = "%$orderNumber%";
    }

    // ORDER BY
    $orderBy = '';
    switch ($sort) {
        case 'order_date':
            $orderBy = "o.order_date " . ($view === 'active' ? "ASC" : "DESC");
            break;
        case 'status':
            $orderBy = "o.status ASC";
            break;
        case 'total':
            $orderBy = "o.total DESC";
            break;
        case 'pickup':
            $orderBy = "CASE WHEN o.delivery_method = 'pickup' THEN 0 ELSE 1 END, o.order_date ASC";
            break;
        case 'home_delivery':
            $orderBy = "CASE WHEN o.delivery_method = 'home' THEN 0 ELSE 1 END, o.order_date ASC";
            break;
        default:
            $orderBy = "o.order_date ASC";
            break;
    }

    $stmt = $pdo->prepare("
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
            o.delivery_method,
            COUNT(oi.order_item_id) AS items_count
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        WHERE $where
        GROUP BY o.order_id
        ORDER BY $orderBy
    ");

    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "orders" => $orders]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
