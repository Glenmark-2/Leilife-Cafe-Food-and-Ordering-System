<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db_script/db.php'; // your PDO $pdo connection

// Only allow GET or POST requests
if ($_SERVER["REQUEST_METHOD"] !== "GET" && $_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

try {
    // Correct SQL to count positive, negative (including LABEL_0), and neutral
$stmt = $pdo->query("
    SELECT 
        SUM(CASE WHEN LOWER(TRIM(sentiment)) = 'positive' THEN 1 ELSE 0 END) AS positive,
        SUM(CASE WHEN LOWER(TRIM(sentiment)) = 'negative' OR LOWER(TRIM(sentiment)) = 'label_0' THEN 1 ELSE 0 END) AS negative,
        SUM(CASE 
                WHEN sentiment IS NOT NULL
                     AND TRIM(sentiment) != ''
                     AND LOWER(TRIM(sentiment)) NOT IN ('positive','negative','label_0')
                THEN 1 
                ELSE 0 
            END) AS neutral
    FROM inbox
");
    
    $counts = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'positive' => (int)$counts['positive'],
        'negative' => (int)$counts['negative'],
        'neutral'  => (int)$counts['neutral'],
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
