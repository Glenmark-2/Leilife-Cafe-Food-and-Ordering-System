<?php
if (!class_exists('AppData')) {
    class AppData {
        private $db;

        public $users = [];
        public $admins = [];
        public $categories = [];
        public $products = [];
        public $orders = [];
        public $feedback = [];

        public function __construct($pdo) {
            $this->db = $pdo;
        }

        // --- Users ---
        public function loadUsers() {
            $stmt = $this->db->query("SELECT user_id, username, first_name, last_name, email, phone_number, created_at FROM users");
            $this->users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // --- Admins ---
        public function loadAdmins() {
            $stmt = $this->db->query("SELECT admin_id, name, email, role, shift, status, created_at FROM admins");
            $this->admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // --- Categories ---
        public function loadCategories() {
            $stmt = $this->db->query("SELECT category_id, category_name, main_category_name FROM categories");
            $this->categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // --- Products ---
        public function loadProducts() {
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
public function adminloadProducts() {
    $stmt = $this->db->query("
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
    ON c.main_category_id = mc.main_category_id;

    ");
    $this->products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}


        // --- Orders ---
        public function loadOrders() {
            $stmt = $this->db->query("SELECT * FROM orders");
            $this->orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // --- Feedback ---
        public function loadFeedback() {
            $stmt = $this->db->query("
                SELECT f.feedback_id, f.comments, f.rating, u.username, p.product_name
                FROM feedback f
                JOIN users u ON f.user_id = u.user_id
                JOIN products p ON f.product_id = p.product_id
            ");
            $this->feedback = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}
