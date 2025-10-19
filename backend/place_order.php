<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db_script/db.php';
require_once __DIR__ . '/create_payment_intent.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$data = json_decode(file_get_contents("php://input"), true);
$payment_method = $data['payment_method'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;
$delivery_method = $data['delivery_method'] ?? null;


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

// Function to generate best-practice order number
function generateOrderNumber() {
    $prefix = "ORD";
    $date = date("Ymd");
    $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)); // 6 random chars
    return "$prefix-$date-$random";
}


try {
    $pdo->beginTransaction();

    // Get user's cart
    $cartQuery = $pdo->prepare("SELECT * FROM carts WHERE user_id = :uid LIMIT 1");
    $cartQuery->execute([':uid' => $user_id]);
    $cart = $cartQuery->fetch(PDO::FETCH_ASSOC);

    if (!$cart) {
        $pdo->rollBack();
        echo json_encode(["success" => false, "message" => "Cart not found."]);
        exit;
    }

    // Generate secure order number
    $order_number = generateOrderNumber();

    // Insert into orders
    $orderStmt = $pdo->prepare("
        INSERT INTO orders (user_id, total, payment_method, payment_status, order_number, delivery_method)
        VALUES (:uid, :total, :payment, :status, :order_number, :delivery_method)
    ");
  
    $orderStmt->execute([
        ':uid'          => $user_id,
        ':total'        => $cart['total'],
        ':payment'      => $payment_method,
        ':status'       => ($payment_method === 'cod') ? 'unpaid' : 'unpaid',
        ':order_number' => $order_number,
        ':delivery_method' => $delivery_method
    ]);
    $order_id = $pdo->lastInsertId();

    // Fetch cart items with product prices
    $cartItemsStmt = $pdo->prepare("
        SELECT ci.cart_item_id, ci.product_id, ci.quantity, ci.size, ci.flavor_ids,
               p.product_price, p.price_large
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.product_id
        WHERE ci.cart_id = :cart_id
    ");
    $cartItemsStmt->execute([':cart_id' => $cart['cart_id']]);
    $cartItems = $cartItemsStmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$cartItems) {
        throw new Exception("No items found in cart.");
    }

    // Insert order items
    $orderItemStmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (:order_id, :product_id, :quantity, :price)
    ");

    foreach ($cartItems as $item) {
        $unitPrice = ($item['size'] === 'large' && $item['price_large'] !== null)
            ? $item['price_large']
            : $item['product_price'];

        $orderItemStmt->execute([
            ':order_id'   => $order_id,
            ':product_id' => $item['product_id'],
            ':quantity'   => $item['quantity'],
            ':price'      => $unitPrice
        ]);
    }

    $pdo->commit();

    if ($payment_method === 'gcash') {
        try {
            $pi = createPaymentIntent($cart['total'], $order_id);
            echo json_encode([
                "success" => true,
                "message" => "Order created, redirecting to PayMongo.",
                "order_id" => $order_id,
                "order_number" => $order_number,
                "checkout_url" => $pi['checkout_url'] ?? null
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "message" => "Payment intent failed: " . $e->getMessage()
            ]);
        }
    } else {
        // COD → clear cart immediately
        $clearCartStmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = :cart_id");
        $clearCartStmt->execute([':cart_id' => $cart['cart_id']]);

        echo json_encode([
            "success" => true,
            "message" => "Order created successfully.",
            "order_id" => $order_id,
            "order_number" => $order_number
        ]);
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        "success" => false,
        "message" => "Order failed: " . $e->getMessage()
    ]);
}
