<?php
// backend/admin/fetch_inbox_sentiment.php
header('Content-Type: application/json');

// Include database
require_once __DIR__ . '/../db_script/db.php';

try {
    // Count by sentiment
    $query = "
        SELECT 
            COALESCE(SUM(CASE WHEN sentiment = 'POSITIVE' THEN 1 ELSE 0 END), 0) AS POSITIVE,
            COALESCE(SUM(CASE WHEN sentiment = 'NEGATIVE' THEN 1 ELSE 0 END), 0) AS NEGATIVE,
            COALESCE(SUM(CASE WHEN sentiment = 'NEUTRAL'  THEN 1 ELSE 0 END), 0) AS NEUTRAL,
            COALESCE(SUM(CASE WHEN sentiment = 'PENDING'  THEN 1 ELSE 0 END), 0) AS PENDING
        FROM inbox
    ";
    $stmt = $pdo->query($query);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($data ?: [
        'POSITIVE' => 0,
        'NEGATIVE' => 0,
        'NEUTRAL' => 0,
        'PENDING' => 0
    ]);
} catch (Throwable $e) {
    // Log and return fallback JSON
    error_log("fetch_inbox_sentiment.php error: " . $e->getMessage());
    echo json_encode([
        'POSITIVE' => 0,
        'NEGATIVE' => 0,
        'NEUTRAL' => 0,
        'PENDING' => 0,
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
