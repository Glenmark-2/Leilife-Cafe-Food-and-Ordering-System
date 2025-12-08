<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/db_script/db.php';

header('Content-Type: application/json');

try {
    // Fetch available payment methods
    $stmt = $pdo->query("SELECT method, status FROM payment_methods");
    $methods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch GCash settings (for display if needed)
    $gcash = $pdo->query("SELECT gcash_name, gcash_number, gcash_email FROM payment_settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'methods' => $methods,
        'gcash' => $gcash
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
