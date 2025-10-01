<?php
// paymongo_webhook.php
require_once __DIR__ . '/db_script/db.php';

header("Content-Type: application/json");

$rawPayload = file_get_contents("php://input");
$event = json_decode($rawPayload, true);

// Always log the full payload (timestamp + payload)
file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " RAW: " . $rawPayload . PHP_EOL, FILE_APPEND);

// Basic validation
if (!$event || !isset($event['data'])) {
    http_response_code(400);
    file_put_contents(__DIR__ . "/paymongo_webhook_error.log", date("Y-m-d H:i:s") . " Invalid or empty JSON payload\n", FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Invalid webhook payload"]);
    exit;
}

// Normalize / extract metadata/status/event
$topType = $event['data']['type'] ?? null; // could be 'event', 'payment_intent', 'payment'
$eventType = null;    // e.g. "payment.paid" or "payment_intent.succeeded"
$inner = [];          // the payment or payment_intent attributes array

// Case: wrapped event (common webhook shape)
if ($topType === 'event') {
    $eventType = $event['data']['attributes']['type'] ?? null; // ex: "payment.paid"
    $inner = $event['data']['attributes']['data']['attributes'] ?? [];
    file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " TopType=event, EventType={$eventType}" . PHP_EOL, FILE_APPEND);
} else {
    // Direct object shape: data.type == "payment_intent" or "payment"
    $eventType = $topType; 
    $inner = $event['data']['attributes'] ?? [];
    file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " TopType={$topType}, EventType={$eventType}" . PHP_EOL, FILE_APPEND);
}

// Try more places just in case
if (empty($inner)) {
    // try a couple fallbacks
    $inner = $event['data']['attributes'] ?? [];
    if (empty($inner) && isset($event['data']['attributes']['data']['attributes'])) {
        $inner = $event['data']['attributes']['data']['attributes'];
    }
    file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " Used fallback for inner attributes" . PHP_EOL, FILE_APPEND);
}

// metadata may sit in different places; pick first found
$metadataCandidates = [
    $inner['metadata'] ?? null,
    $event['data']['attributes']['metadata'] ?? null,
    $event['data']['attributes']['data']['attributes']['metadata'] ?? null
];

$metadata = null;
foreach ($metadataCandidates as $m) {
    if (is_array($m) && !empty($m)) { 
        $metadata = $m; 
        break; 
    }
}

$orderId = null;
if (is_array($metadata)) {
    $orderId = $metadata['order_id'] ?? $metadata['reference_number'] ?? null;
}
file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " Extracted orderId={$orderId}" . PHP_EOL, FILE_APPEND);

$status = $inner['status'] ?? null; // e.g. "paid" or "succeeded"
file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " Extracted status={$status}" . PHP_EOL, FILE_APPEND);

if (!$orderId) {
    file_put_contents(__DIR__ . "/paymongo_webhook_error.log", date("Y-m-d H:i:s") . " Missing order_id in payload: " . $rawPayload . PHP_EOL, FILE_APPEND);
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing order identifier in webhook"]);
    exit;
}

// Decide success/failure robustly
$paidStatuses = ['paid', 'succeeded'];
$failedStatuses = ['failed', 'cancelled', 'canceled'];

$isSuccess = in_array(strtolower($status ?? ''), $paidStatuses)
             || in_array(strtolower($eventType ?? ''), ['payment.paid', 'payment_intent.succeeded']);

$isFailure = in_array(strtolower($status ?? ''), $failedStatuses)
             || in_array(strtolower($eventType ?? ''), ['payment.failed', 'payment_intent.payment_failed']);

file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " Condition check: isSuccess=" . ($isSuccess ? 'yes' : 'no') . ", isFailure=" . ($isFailure ? 'yes' : 'no') . PHP_EOL, FILE_APPEND);

// Update DB accordingly
try {
    $pdo->beginTransaction();

    if ($isSuccess) {
        $stmt = $pdo->prepare("UPDATE orders 
                               SET payment_status = 'paid', status = 'preparing' 
                               WHERE order_id = :oid");
        $stmt->execute([':oid' => $orderId]);

        $clearCartStmt = $pdo->prepare("
            DELETE ci FROM cart_items ci
            JOIN carts c ON ci.cart_id = c.cart_id
            JOIN orders o ON o.user_id = c.user_id
            WHERE o.order_id = :oid
        ");
        $clearCartStmt->execute([':oid' => $orderId]);

        file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " ✅ Order {$orderId} marked paid and cart cleared" . PHP_EOL, FILE_APPEND);

    } elseif ($isFailure) {
        $stmt = $pdo->prepare("UPDATE orders 
                               SET payment_status = 'failed' 
                               WHERE order_id = :oid");
        $stmt->execute([':oid' => $orderId]);
        file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " ❌ Order {$orderId} marked failed" . PHP_EOL, FILE_APPEND);
    } else {
        file_put_contents(__DIR__ . "/paymongo_webhook.log", date("Y-m-d H:i:s") . " ℹ️ Non-terminal webhook for order {$orderId}: eventType={$eventType}, status={$status}" . PHP_EOL, FILE_APPEND);
    }

    $pdo->commit();
    echo json_encode(["success" => true]);
    http_response_code(200);
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    file_put_contents(__DIR__ . "/paymongo_webhook_error.log", date("Y-m-d H:i:s") . " Exception: " . $e->getMessage() . PHP_EOL . "Payload: " . $rawPayload . PHP_EOL, FILE_APPEND);
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
    exit;
}
