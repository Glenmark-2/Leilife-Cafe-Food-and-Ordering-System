<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db_script/db.php';
require_once __DIR__ . '/create_payment_intent.php';

if (session_status() === PHP_SESSION_NONE) session_start();

/* -------------------------------------------
   Handle both JSON and FormData requests
------------------------------------------- */
$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);
if (!$data && !empty($_POST)) {
    $data = $_POST; // Fallback for FormData
}

$payment_method = $data['payment_method'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;
$delivery_method = $data['delivery_method'] ?? null;

/* -------------------------------------------
   Basic Validation
------------------------------------------- */
if (!$user_id) {
    echo json_encode(["success" => false, "message" => "User not logged in."]);
    exit;
}

if (!$payment_method) {
    echo json_encode(["success" => false, "message" => "No payment method selected."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

/* -------------------------------------------
   Generate Order Number
------------------------------------------- */
function generateOrderNumber() {
    return "ORD-" . date("Ymd") . "-" . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
}

try {
    $pdo->beginTransaction();

    // Fetch user's cart
    $cartQuery = $pdo->prepare("SELECT * FROM carts WHERE user_id = :uid LIMIT 1");
    $cartQuery->execute([':uid' => $user_id]);
    $cart = $cartQuery->fetch(PDO::FETCH_ASSOC);

    if (!$cart) {
        $pdo->rollBack();
        echo json_encode(["success" => false, "message" => "Cart not found."]);
        exit;
    }

    $order_number = generateOrderNumber();

    // Insert into orders
    $orderStmt = $pdo->prepare("
        INSERT INTO orders (user_id, total, payment_method, payment_status, order_number, delivery_method, status)
        VALUES (:uid, :total, :payment, :status, :order_number, :delivery_method, 'pending')
    ");
    $orderStmt->execute([
        ':uid' => $user_id,
        ':total' => $cart['total'],
        ':payment' => $payment_method,
        ':status' => 'unpaid',
        ':order_number' => $order_number,
        ':delivery_method' => $delivery_method
    ]);

    $order_id = $pdo->lastInsertId();

    // Fetch cart items
    $cartItemsStmt = $pdo->prepare("
        SELECT ci.cart_item_id, ci.product_id, ci.quantity, ci.size, ci.flavor_ids,
               p.product_price, p.price_large, p.has_flavor
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.product_id
        WHERE ci.cart_id = :cart_id
    ");
    $cartItemsStmt->execute([':cart_id' => $cart['cart_id']]);
    $cartItems = $cartItemsStmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$cartItems) throw new Exception("No items in cart.");

    // Insert each order item
    $orderItemStmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price, size, flavor_ids)
        VALUES (:order_id, :product_id, :quantity, :price, :size, :flavor_ids)
    ");

    foreach ($cartItems as $item) {
        $unitPrice = ($item['size'] === 'large' && $item['price_large'] !== null)
            ? $item['price_large']
            : $item['product_price'];

        $flavorCsv = ($item['has_flavor'] && !empty($item['flavor_ids']))
            ? $item['flavor_ids']
            : null;

        $orderItemStmt->execute([
            ':order_id' => $order_id,
            ':product_id' => $item['product_id'],
            ':quantity' => $item['quantity'],
            ':price' => $unitPrice,
            ':size' => $item['size'] ?? null,
            ':flavor_ids' => $flavorCsv
        ]);
    }

    $pdo->commit();

    /* -------------------------------------------
       Payment Handling
    ------------------------------------------- */
    $response = [];

    if ($payment_method === 'gcash') {
        // GCash / PayMongo flow
        $pi = createPaymentIntent($cart['total'], $order_id);

        $response = [
            "success" => true,
            "message" => "Order created, redirecting to PayMongo.",
            "order_id" => $order_id,
            "order_number" => $order_number,
            "checkout_url" => $pi['checkout_url'] ?? null
        ];
    } else {
        // Cash on Delivery (COD)
        $clearCartStmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = :cart_id");
        $clearCartStmt->execute([':cart_id' => $cart['cart_id']]);

        $host = $_SERVER['HTTP_HOST'];
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";

        // Auto-detect URL for local or deployed
        $redirectUrl = "$protocol://$host/Leilife/public/index.php?page=order-tracking&num=" . urlencode($order_number);

        $response = [
            "success" => true,
            "message" => "Order created successfully.",
            "order_id" => $order_id,
            "order_number" => $order_number,
            "redirect_url" => $redirectUrl
        ];
    }

    echo json_encode($response);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(["success" => false, "message" => "Order failed: " . $e->getMessage()]);
}
