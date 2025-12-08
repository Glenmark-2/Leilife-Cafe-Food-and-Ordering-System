<?php
// initiate_refund.php
require_once __DIR__ . '/db_script/db.php';
require_once __DIR__ . '/create_payment_intent.php';
session_start();

header('Content-Type: application/json');

$order_id = $_POST['order_id'] ?? null;
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : null;
$user_id = $_SESSION['user_id'] ?? null;

$response = ['success' => false, 'message' => '', 'debug' => []];

if (!$order_id || !$user_id || $amount === null) {
    $response['message'] = 'Missing parameters';
    echo json_encode($response);
    exit;
}

try {
    // ensure order belongs to user (or user has rights)
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$order) throw new Exception("Order not found or not owned.");

    // Only allow refund if order was paid
    if (($order['payment_status'] ?? '') !== 'paid') {
        throw new Exception("Order payment is not in 'paid' state.");
    }

    $pdo->beginTransaction();

    // find payment id
    $effective_payment_id = $order['payment_id'] ?? null;
    if (empty($effective_payment_id)) {
        $tx = $pdo->prepare("SELECT external_id FROM transactions WHERE order_id = ? AND transaction_name='payment' AND transaction_status='success' ORDER BY transaction_id DESC LIMIT 1");
        $tx->execute([$order_id]);
        $t = $tx->fetch(PDO::FETCH_ASSOC);
        if ($t) $effective_payment_id = $t['external_id'] ?? null;
    }

    if (empty($effective_payment_id)) {
        throw new Exception("No payment identifier available to issue refund.");
    }

    // check refundable
    $remaining_centavos = getRemainingRefundable($effective_payment_id);
    $requested_centavos = intval(round($amount * 100));
    if ($requested_centavos > $remaining_centavos) {
        $requested_centavos = $remaining_centavos;
    }
    if ($requested_centavos <= 0) {
        throw new Exception("No refundable balance left at provider.");
    }

    $refund_pesos = $requested_centavos / 100.0;

    // initiate refund at provider
    $refund_response = createRefund($effective_payment_id, $refund_pesos, $order_id);
    if (!is_array($refund_response) || empty($refund_response['data']['id'])) {
        throw new Exception("Invalid refund response from provider.");
    }

    // Do NOT insert a transactions refund record here. PayMongo webhook will insert/update it.
    $pdo->commit();

    $response['success'] = true;
    $response['message'] = 'Refund initiated';
    $response['refund'] = [
        'id' => $refund_response['data']['id'],
        'status' => $refund_response['data']['attributes']['status'] ?? 'pending',
        'amount' => $refund_pesos
    ];

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $response['success'] = false;
    $response['message'] = 'Refund initiation failed: ' . $e->getMessage();
    $response['debug'][] = $e->getTraceAsString();
}

echo json_encode($response);
