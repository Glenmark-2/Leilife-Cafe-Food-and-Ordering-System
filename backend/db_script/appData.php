<?php
if (!class_exists('AppData')) {
    class AppData
    {
        private $db;

        public $users = [];
        public $admins = [];
        public $categories = [];
        public $products = [];
        public $orders = [];
        public $feedback = [];

        public function __construct($pdo)
        {
            $this->db = $pdo;
        }

        // --- Users ---
        public function loadUsers()
        {
            $stmt = $this->db->query("SELECT user_id, username, first_name, last_name, email, phone_number, created_at FROM users");
            $this->users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // --- Admins ---
        public function loadAdmins()
        {
            $stmt = $this->db->query("SELECT admin_id, name, email, role, shift, status, created_at FROM admins");
            $this->admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // --- Categories ---
        public function loadCategories()
        {
            $stmt = $this->db->query("SELECT category_id, category_name, main_category_name FROM categories");
            $this->categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // --- Products ---
        public function loadProducts()
        {
            $stmt = $this->db->query("
        SELECT p.product_id, 
               p.category_id,   
               p.product_name, 
               p.product_price, 
               p.price_large,
               p.status, 
               p.product_picture,
               c.category_name, 
               c.main_category_name
        FROM products p
        JOIN categories c ON p.category_id = c.category_id
    ");
            $this->products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function adminloadProducts($archived = false)
        {
            // 0 = active, 1 = archived
            $isArchive = $archived ? 1 : 0;

            $stmt = $this->db->prepare("
        SELECT DISTINCT
            p.product_id, 
            p.category_id,   
            p.product_name, 
            p.product_price, 
            p.price_large,
            p.status, 
            p.product_picture,
            c.category_name, 
            c.main_category_id,
            mc.main_category_name
        FROM products p
        JOIN categories c 
            ON p.category_id = c.category_id
        JOIN categories mc
            ON c.main_category_id = mc.main_category_id
        WHERE p.is_archive = :is_archive
    ");
            $stmt->execute(['is_archive' => $isArchive]);
            $this->products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // --- Orders ---
        public function loadOrders()
        {
            $stmt = $this->db->query("SELECT * FROM orders");
            $this->orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // --- Feedback ---
        public function loadFeedback()
        {
            $stmt = $this->db->query("
                SELECT f.feedback_id, f.comments, f.rating, u.username, p.product_name
                FROM feedback f
                JOIN users u ON f.user_id = u.user_id
                JOIN products p ON f.product_id = p.product_id
            ");
            $this->feedback = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // --- user info ---
        public function loadUserInfo($user_id)
        {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // --- user info ---
        public function loadUserAddress($user_id)
        {
            $stmt = $this->db->prepare("SELECT * FROM addresses WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        function userHasPassword(int $userId): bool
        {

            $stmt = $this->db->prepare("SELECT password_hash FROM users WHERE user_id = :id LIMIT 1");
            $stmt->execute([':id' => $userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return ($row && !empty($row['password_hash']));
        }

        public function loadInbox($archived = 0)
        {
            $stmt = $this->db->prepare("SELECT * FROM inbox WHERE is_archived = :archived ORDER BY created_at DESC");
            $stmt->bindParam(":archived", $archived, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function loadMessagesToday($archived = 0)
        {
            $stmt = $this->db->prepare("SELECT * FROM inbox WHERE is_archived = :archived AND DATE(created_at) = CURDATE() ORDER BY created_at DESC");
            $stmt->bindParam(":archived", $archived, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function loadFeaturedProducts()
        {
            $stmt = $this->db->prepare("
            SELECT 
                c.main_category_id,
                c.main_category_name,
                p.*
            FROM products p
            LEFT JOIN categories c 
                ON p.category_id = c.category_id
            where main_category_id = 3;
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function loadUsersFave($user_id)
        {
            $stmt = $this->db->prepare("SELECT * from favorites where user_id = :user_id");
            $stmt->execute(['user_id' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        public function getProductById($product_id)
        {
            $stmt = $this->db->prepare("SELECT * FROM products WHERE product_id = :id");
            $stmt->execute(['id' => $product_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        public function getOrderOfUser($user_id)
        {
            // Step 1: get the latest order_id of this user
            $stmt = $this->db->prepare("
                SELECT order_id 
                FROM orders 
                WHERE user_id = :user_id 
                ORDER BY order_id DESC 
                LIMIT 1
            ");
            $stmt->execute(['user_id' => $user_id]);
            $latestOrderId = $stmt->fetchColumn();

            if (!$latestOrderId) {
                return []; // no orders
            }

            // Step 2: get all items of that order
            $stmt = $this->db->prepare("
                SELECT 
                    o.order_id, 
                    o.status, 
                    o.payment_method, 
                    o.payment_status,
                    o.total,
                    o.order_date,
                    oi.quantity, 
                    oi.price,
                    p.product_name
                FROM orders o
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                LEFT JOIN products p ON oi.product_id = p.product_id
                WHERE o.user_id = :user_id
                  AND o.order_id = :order_id
            ");
            $stmt->execute([
                'user_id' => $user_id,
                'order_id' => $latestOrderId
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
public function getActiveOrdersOfUser($user_id)
{
    $stmt = $this->db->prepare("
        SELECT o.order_id, o.order_number, o.order_date, o.status,
               o.payment_method, o.total, p.product_name
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN products p ON oi.product_id = p.product_id
        WHERE o.user_id = :uid
          AND o.status != 'delivered'
        ORDER BY o.order_date DESC
    ");
    $stmt->execute(['uid' => $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
        public function getOrderById($order_id, $user_id)
        {
            $stmt = $this->db->prepare("
                SELECT o.*, 
                       oi.product_id, oi.quantity, oi.price, 
                       p.product_name, p.product_image
                FROM orders o
                JOIN order_items oi ON o.order_id = oi.order_id
                JOIN products p ON oi.product_id = p.product_id
                WHERE o.order_id = :oid AND o.user_id = :uid
            ");
            $stmt->execute([
                ':oid' => $order_id,
                ':uid' => $user_id
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
public function getOrderByNumber($user_id, $order_number)
{
    // 1️⃣ Fetch order and items
    $stmt = $this->db->prepare("
        SELECT o.*, 
               oi.order_item_id, oi.product_id, oi.quantity, oi.price, oi.size, oi.flavor_ids,
               p.product_name, p.has_flavor, p.has_size
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN products p ON oi.product_id = p.product_id
        WHERE o.order_number = :onum AND o.user_id = :uid
    ");
    $stmt->execute([
        ':onum' => $order_number,
        ':uid'  => $user_id
    ]);

    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$orderItems) return [];

    // 2️⃣ Resolve flavor names for each item
    foreach ($orderItems as &$item) {
        if ($item['has_flavor'] && !empty($item['flavor_ids'])) {
            $ids = explode(',', $item['flavor_ids']);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $flavorStmt = $this->db->prepare("SELECT flavor_name FROM product_flavors WHERE flavor_id IN ($placeholders)");
            $flavorStmt->execute($ids);
            $item['flavors'] = $flavorStmt->fetchAll(PDO::FETCH_COLUMN);
        } else {
            $item['flavors'] = [];
        }
    }

    return $orderItems;
}


        //getting sales today
        public function getSalesToday()
        {
            $stmt = $this->db->prepare("
                SELECT IFNULL(SUM(total), 0) AS total_sales_today
                FROM orders
                WHERE DATE(order_date) = CURDATE() AND status = 'delivered'");
            $stmt->execute();
            return (float)$stmt->fetchColumn();
        }


        // getting sales this week
        public function getSalesThisWeek()
        {
            $stmt = $this->db->prepare("
        SELECT SUM(total) AS total_sales_week
        FROM orders
        WHERE YEARWEEK(order_date, 1) = YEARWEEK(CURDATE(), 1)
        AND status = 'delivered'
    ");
            $stmt->execute();
            return (float)$stmt->fetchColumn();
        }

        // getting sales this month
        public function getSalesThisMonth()
        {
            $stmt = $this->db->prepare("
        SELECT SUM(total) AS total_sales_month
        FROM orders
        WHERE YEAR(order_date) = YEAR(CURDATE())
        AND MONTH(order_date) = MONTH(CURDATE())
        AND status = 'delivered'
    ");
            $stmt->execute();
            return (float)$stmt->fetchColumn();
        }

        // getting sales this year
        public function getSalesThisYear()
        {
            $stmt = $this->db->prepare("
        SELECT SUM(total) AS total_sales_year
        FROM orders
        WHERE YEAR(order_date) = YEAR(CURDATE())
        AND status = 'delivered'
    ");
            $stmt->execute();
            return (float)$stmt->fetchColumn();
        }

        //getting the top 3 sold products

        public function topProducts()
        {
            $stmt = $this->db->prepare("
                SELECT 
                    p.product_name,
                    SUM(oi.quantity) AS total_sold
                FROM 
                    order_items oi
                JOIN 
                    products p ON oi.product_id = p.product_id
                JOIN 
                    orders o ON oi.order_id = o.order_id
                WHERE 
                    o.status = 'delivered'
                GROUP BY 
                    p.product_id, p.product_name
                ORDER BY 
                    total_sold DESC
                LIMIT 3
            ");

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        //total orders per day depending on sttatus
        public function getTodayOrdersByStatus()
        {
            $stmt = $this->db->prepare("
        SELECT 
            status,
            COUNT(*) AS total_orders
        FROM orders
        WHERE DATE(order_date) = CURDATE()
        GROUP BY status
    ");

            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Prepare default counts to ensure all statuses exist even if 0
            $counts = [
                'pending' => 0,
                'preparing' => 0,
                'ready_for_delivery' => 0,
                'delivered' => 0,
                'cancelled' => 0
            ];

            foreach ($results as $row) {
                $counts[$row['status']] = $row['total_orders'];
            }

            return $counts;
        }

        //total active admin
        public function activeAdmin()
        {
            $stmt = $this->db->prepare("
            select sum(is_active) as total_active_admin
            from admin_accounts
            where is_active = 1;");
            $stmt->execute();
            return  $stmt->fetchColumn();
        }

        //total active driver
        public function activeDriver()
        {
            $stmt = $this->db->prepare("
            select sum(is_active) as total_active_driver
            from driver_accounts
            where is_active = 1;");
            $stmt->execute();
            return  $stmt->fetchColumn();
        }


        //cancel order
        public function cancelOrder($order_number)
        {
            $stmt = $this->db->prepare("
            UPDATE orders
            SET status = 'cancelled'
            WHERE order_number =:order_number");

            $stmt->execute([':order_number' => $order_number]);
        }


        /**
         * Get orders filtered by status, payment method, and date range
         * 
         * @param int|null $userId Optional. If provided, filter orders for this user only.
         * @param string $status "All" or specific status like "Pending", "Delivered"
         * @param string $payment "All" or "Cash", "Gcash", etc.
         * @param string|null $fromDate Format "YYYY-MM-DD"
         * @param string|null $toDate Format "YYYY-MM-DD"
         * @return array
         */
        public function getOrdersByFilters($userId = null, $status = 'All', $payment = 'All', $fromDate = null, $toDate = null)
        {
            $params = [];
            $where = [];

            if ($userId !== null) {
                $where[] = "o.user_id = :user_id";
                $params[':user_id'] = $userId;
            }

            if ($status !== 'All') {
                $where[] = "o.status = :status";
                $params[':status'] = $status;
            }

            if ($payment !== 'All') {
                $where[] = "o.payment_method = :payment";
                $params[':payment'] = $payment;
            }

            if ($fromDate) {
                $where[] = "DATE(o.order_date) >= :fromDate";
                $params[':fromDate'] = $fromDate;
            }
            if ($toDate) {
                $where[] = "DATE(o.order_date) <= :toDate";
                $params[':toDate'] = $toDate;
            }

            // Join users table for customer name
            $sql = "SELECT o.order_id, o.order_number, o.status, o.payment_method, o.total, 
                   o.order_date AS date, 
                   CONCAT(u.first_name, ' ', u.last_name) AS customer_name
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.user_id";

            if (!empty($where)) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }

            $sql .= " ORDER BY o.order_date DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch order items with product name
            foreach ($orders as &$order) {
                $stmtItems = $this->db->prepare("
            SELECT p.product_name, oi.quantity
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.product_id
            WHERE oi.order_id = :order_id
        ");
                $stmtItems->execute([':order_id' => $order['order_id']]);
                $order['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
            }

            return $orders;
        }


public function loadUserOrders($userId)
{
    if (!$userId) return [];

    $stmt = $this->db->prepare("
        SELECT o.order_id, o.order_number, o.status, o.payment_method, o.total, o.order_date AS date
        FROM orders o
        WHERE o.user_id = :user_id
        ORDER BY o.order_date DESC
    ");
    $stmt->execute([':user_id' => $userId]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($orders as &$order) {
        // Fetch order items with size and flavors
        $stmtItems = $this->db->prepare("
            SELECT 
                oi.order_item_id, oi.quantity, oi.price, oi.size, oi.flavor_ids,
                p.product_name
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.product_id
            WHERE oi.order_id = :order_id
        ");
        $stmtItems->execute([':order_id' => $order['order_id']]);
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as &$item) {
            $item['flavors'] = [];
            if (!empty($item['flavor_ids'])) {
                $flavorIds = explode(',', $item['flavor_ids']);
                $placeholders = implode(',', array_fill(0, count($flavorIds), '?'));
                $stmtFlavors = $this->db->prepare("
                    SELECT flavor_name 
                    FROM product_flavors 
                    WHERE flavor_id IN ($placeholders)
                ");
                $stmtFlavors->execute($flavorIds);
                $item['flavors'] = array_column($stmtFlavors->fetchAll(PDO::FETCH_ASSOC), 'flavor_name');
            }
        }

        $order['items'] = $items;

        // Fetch review for this order
        $stmtReview = $this->db->prepare("
            SELECT message 
            FROM inbox 
            WHERE subject = :subject
            LIMIT 1
        ");
        $subject = "Order Review #{$order['order_number']}";
        $stmtReview->execute([':subject' => $subject]);
        $review = $stmtReview->fetch(PDO::FETCH_ASSOC);
        $order['review'] = $review['message'] ?? null;
    }

    return $orders;
}



        //for sales report
public function getSalesSummary($fromDate = null, $toDate = null, $status = null, $payment = null)
{
    $params = [];
    $where = [];

    // 📅 Date filters
    if ($fromDate) {
        $where[] = "DATE(o.order_date) >= :fromDate";
        $params[':fromDate'] = $fromDate;
    }
    if ($toDate) {
        $where[] = "DATE(o.order_date) <= :toDate";
        $params[':toDate'] = $toDate;
    }

    // 🟢 Status filter
    if ($status && strtolower($status) !== 'all') {
        $where[] = "LOWER(o.status) = :status";
        $params[':status'] = strtolower($status);
    }

    // 🟢 Payment filter
    if ($payment && strtolower($payment) !== 'all') {
        $where[] = "LOWER(o.payment_method) = :payment";
        $params[':payment'] = strtolower($payment);
    }

    $whereSQL = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    // === 🧾 SALES SUMMARY ===


$sqlSummary = "
    SELECT 
        COUNT(DISTINCT o.order_id) AS total_orders,
        COALESCE(SUM(o.total), 0) AS total_revenue, -- includes other charges
        COALESCE(SUM(oi.quantity * oi.price), 0) AS total_product_revenue -- only products
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    $whereSQL
";



    $stmt = $this->db->prepare($sqlSummary);
    $stmt->execute($params);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);

    // === 🏆 TOP 5 PRODUCTS ===
    $sqlTop5 = "
        SELECT 
            p.product_name,
            COUNT(oi.order_id) AS orders,
            SUM(oi.quantity * oi.price) AS revenue
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        JOIN orders o ON o.order_id = oi.order_id
        $whereSQL
        GROUP BY p.product_id
        ORDER BY revenue DESC
        LIMIT 5
    ";
    $stmtTop5 = $this->db->prepare($sqlTop5);
    $stmtTop5->execute($params);
    $topProducts = $stmtTop5->fetchAll(PDO::FETCH_ASSOC);

    // === 🗂 MAIN CATEGORIES (correct revenue per filtered orders) ===
    $sqlMain = "
        SELECT 
            mc.main_category_name AS category,
            COUNT(DISTINCT o.order_id) AS total_orders,
            COALESCE(SUM(oi.price * oi.quantity), 0) AS total_revenue
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN products p ON oi.product_id = p.product_id
        JOIN categories sc ON p.category_id = sc.category_id
        JOIN (
            SELECT main_category_id, main_category_name
            FROM categories
            GROUP BY main_category_id, main_category_name
        ) mc ON sc.main_category_id = mc.main_category_id
        $whereSQL
        GROUP BY mc.main_category_id, mc.main_category_name
        ORDER BY total_revenue DESC
    ";
    $stmtMain = $this->db->prepare($sqlMain);
    $stmtMain->execute($params);
    $mainCategories = $stmtMain->fetchAll(PDO::FETCH_ASSOC);

    // === 📊 SUBCATEGORIES (TABLE PER CATEGORY, PRODUCT LISTED WITH SOLD PRICE) ===
    $sqlSub = "
        SELECT 
            sc.category_id,
            sc.category_name,
            p.product_name,
            oi.price AS sold_price,
            SUM(oi.quantity) AS total_quantity,
            COUNT(DISTINCT o.order_id) AS total_orders,
            SUM(oi.quantity * oi.price) AS total_revenue
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        JOIN categories sc ON p.category_id = sc.category_id
        JOIN orders o ON o.order_id = oi.order_id
        $whereSQL
        GROUP BY sc.category_id, sc.category_name, p.product_id, p.product_name, oi.price
        ORDER BY sc.category_name, total_revenue DESC
    ";
    $stmtSub = $this->db->prepare($sqlSub);
    $stmtSub->execute($params);
    $rows = $stmtSub->fetchAll(PDO::FETCH_ASSOC);

    // 🧩 Group products under their subcategory
    $subCategories = [];
    foreach ($rows as $r) {
        $cat = $r['category_name'] ?? 'Uncategorized';
        if (!isset($subCategories[$cat])) {
            $subCategories[$cat] = [];
        }
        $subCategories[$cat][] = [
            'product_name' => $r['product_name'],
            'sold_price' => $r['sold_price'],
            'total_quantity' => $r['total_quantity'],
            'total_orders' => $r['total_orders'],
            'total_revenue' => $r['total_revenue']
        ];
    }

    return [
        'summary' => $summary,
        'top_products' => $topProducts,
        'main_categories' => $mainCategories,
        'sub_categories' => $subCategories
    ];
}


public function reorder($order_id, $user_id, $session_id, $option_type = 'delivery') {
    // 1) Fetch order items joined with product data
    $stmt = $this->db->prepare("
        SELECT oi.*, p.product_name, p.status AS product_status, p.product_price AS base_price, 
               p.price_large, p.has_size, p.has_flavor
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = :oid
    ");
    $stmt->execute([':oid' => $order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$items) {
        return [
            'success' => false,
            'message' => 'No products found in this order.',
            'availableCount' => 0
        ];
    }

    $availableProducts = [];

    // 2) Validate each product availability
    foreach ($items as $it) {
        // Check product availability
        if (strtolower($it['product_status']) !== 'available') {
            continue;
        }

        // Check size availability
        if (!empty($it['size']) && intval($it['has_size']) === 1) {
            $stmtSize = $this->db->prepare("
                SELECT status FROM drink_size WHERE product_id = :pid AND size_name = :size LIMIT 1
            ");
            $stmtSize->execute([':pid' => $it['product_id'], ':size' => $it['size']]);
            $sizeStatus = $stmtSize->fetchColumn();
            if ($sizeStatus !== 'available') {
                continue;
            }
        }

        // Check flavor availability
        if (!empty($it['flavor_ids']) && intval($it['has_flavor']) === 1) {
            $flavorIds = array_filter(array_map('intval', explode(',', $it['flavor_ids'])));
            if (!empty($flavorIds)) {
                $placeholders = implode(',', array_fill(0, count($flavorIds), '?'));
                $sql = "SELECT COUNT(*) FROM product_flavors 
                        WHERE flavor_id IN ($placeholders) AND status='available'";
                $stmtFl = $this->db->prepare($sql);
                $stmtFl->execute($flavorIds);
                $availableCount = (int)$stmtFl->fetchColumn();
                if ($availableCount !== count($flavorIds)) {
                    continue;
                }
            }
        }

        // Passed all checks
        $availableProducts[] = $it;
    }

    // 3) Handle availability results
    if (empty($availableProducts)) {
        return [
            'success' => false,
            'message' => 'All products in this order are unavailable.',
            'availableCount' => 0
        ];
    }

    $totalItems = count($items);
    $availableCount = count($availableProducts);

    $message = 'Reorder added to cart successfully.';
    $success = true;

    // ⚠️ Partial success detection
    if ($availableCount < $totalItems) {
        $message = 'Some products from your previous order were unavailable and were not added.';
    }

    // 4) Compute subtotal
    $sub_total = 0.0;
    foreach ($availableProducts as $ap) {
        $price = $ap['base_price'];
        if (!empty($ap['size']) && $ap['size'] === 'large' && isset($ap['price_large']) && $ap['price_large'] > 0) {
            $price = $ap['price_large'];
        }
        $sub_total += ($price * (int)$ap['quantity']);
    }

    // 5) Find or create cart
    $stmtCart = $this->db->prepare("SELECT cart_id FROM carts WHERE user_id=:uid OR session_id=:sid LIMIT 1");
    $stmtCart->execute([':uid' => $user_id, ':sid' => $session_id]);
    $cart = $stmtCart->fetch(PDO::FETCH_ASSOC);

    if ($cart) {
        $cart_id = $cart['cart_id'];
        $this->db->prepare("DELETE FROM cart_items WHERE cart_id = :cart_id")
            ->execute([':cart_id' => $cart_id]);
        $this->db->prepare("
            UPDATE carts 
            SET sub_total = :sub, total = :total, option_type = :opt 
            WHERE cart_id = :cid
        ")->execute([
            ':sub' => $sub_total,
            ':total' => $sub_total,
            ':opt' => $option_type,
            ':cid' => $cart_id
        ]);
    } else {
        $this->db->prepare("
            INSERT INTO carts (user_id, session_id, option_type, sub_total, total) 
            VALUES (:uid, :sid, :opt, :sub, :total)
        ")->execute([
            ':uid' => $user_id,
            ':sid' => $session_id,
            ':opt' => $option_type,
            ':sub' => $sub_total,
            ':total' => $sub_total
        ]);
        $cart_id = $this->db->lastInsertId();
    }

    // 6) Insert available products into cart_items
    $stmtInsert = $this->db->prepare("
        INSERT INTO cart_items (cart_id, product_id, quantity, size, flavor_ids)
        VALUES (:cart_id, :product_id, :quantity, :size, :flavor_ids)
    ");
    foreach ($availableProducts as $ap) {
        $stmtInsert->execute([
            ':cart_id' => $cart_id,
            ':product_id' => $ap['product_id'],
            ':quantity' => $ap['quantity'],
            ':size' => $ap['size'] ?? null,
            ':flavor_ids' => $ap['flavor_ids'] ?? null
        ]);
    }

    // 7) Return with proper status
    return [
        'success' => $success,
        'message' => $message,
        'availableCount' => $availableCount,
        'totalItems' => $totalItems
    ];
}




public function loadFlavors() {
    $stmt = $this->db->prepare("SELECT * FROM product_flavors");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function loadSizes() {
    $stmt = $this->db->prepare("SELECT * FROM drink_size");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getReviewMessage($orderNumber)
{
    $sql = "SELECT message 
            FROM inbox 
            WHERE subject = :subject 
            LIMIT 1";

    $stmt = $this->db->prepare($sql);
    $subject = "Order Review #{$orderNumber}";
    $stmt->execute(['subject' => $subject]);

    return $stmt->fetchColumn(); // returns message or false if none
}


    }
}
