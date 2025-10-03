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
                SELECT 
                    o.order_id,
                    o.order_number,
                    o.order_date,          -- ✅ include this
                    o.status,
                    o.payment_method,
                    o.payment_status,
                    o.total,
                    oi.quantity,
                    oi.price,
                    p.product_name
                FROM orders o
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                LEFT JOIN products p ON oi.product_id = p.product_id
                WHERE o.user_id = :uid
                ORDER BY o.order_date DESC
            ");
            $stmt->execute([':uid' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        public function getOrderById($order_id, $user_id) {
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
        public function getOrderByNumber($user_id, $order_number) {
            $stmt = $this->db->prepare("
                SELECT o.*, 
                       oi.product_id, oi.quantity, oi.price, 
                       p.product_name
                FROM orders o
                JOIN order_items oi ON o.order_id = oi.order_id
                JOIN products p ON oi.product_id = p.product_id
                WHERE o.order_number = :onum AND o.user_id = :uid
            ");
            $stmt->execute([
                ':onum' => $order_number,
                ':uid'  => $user_id
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

    }
}
