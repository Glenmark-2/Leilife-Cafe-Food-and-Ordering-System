<?php
require_once __DIR__ . '/db_script/db.php';
header('Content-Type: application/json');

// Load your PayMongo secret key (from env or config)
$secretKey = getenv("PAYMONGO_SECRET_KEY");

$input = json_decode(file_get_contents('php://input'), true);
$orderId = $input['order_id'] ?? null;

if (!$orderId) {
    echo json_encode(['success' => false, 'message' => 'Missing order_id']);
    exit;
}

try {
    // Get payment_id from your database
    $stmt = $pdo->prepare("SELECT payment_id, total FROM orders WHERE order_id = :oid");
    $stmt->execute([':oid' => $orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    $paymentId = $order['payment_id'];
    $amount = intval($order['total'] * 100); // PayMongo uses centavos

    // Prepare refund payload
    $payload = [
        'data' => [
            'attributes' => [
                'amount' => $amount,
                'notes' => 'Customer refund for order ' . $orderId,
                'payment_id' => $paymentId
            ]
        ]
    ];

    $ch = curl_init('https://api.paymongo.com/v1/refunds');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Basic ' . base64_encode($secretKey . ':'),
            'Content-Type: application/json'
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    file_put_contents(__DIR__ . "/refunds.log", date("Y-m-d H:i:s") . " Refund response: {$response}" . PHP_EOL, FILE_APPEND);

    if ($httpCode >= 200 && $httpCode < 300) {
        echo json_encode(['success' => true, 'message' => 'Refund initiated', 'data' => json_decode($response, true)]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create refund', 'response' => $response]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
