<?php
session_start();
require_once './db_script/init.php';

$raw = file_get_contents("php://input");
$cart = json_decode($raw, true);

if (!is_array($cart)) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart data']);
    exit;
}

// Save session cart
$_SESSION['cart'] = $cart;

$sessionId = session_id();
$userId = $_SESSION['user_id'] ?? null;

// Find or create cart
$sql = "SELECT cart_id FROM carts WHERE session_id = :sid OR (user_id = :uid AND :uid IS NOT NULL) LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute(['sid' => $sessionId, 'uid' => $userId]);
$cartRow = $stmt->fetch();

if ($cartRow) {
    $cartId = $cartRow['cart_id'];
} else {
    $stmt = $pdo->prepare("INSERT INTO carts 
        (session_id, user_id, option_type, sub_total, delivery_fee, total, created_at, updated_at)
        VALUES (:sid, :uid, 'delivery', 0, 50, 50, NOW(), NOW())");
    $stmt->execute(['sid' => $sessionId, 'uid' => $userId]);
    $cartId = $pdo->lastInsertId();
}

// Empty cart → clear DB
if (empty($cart)) {
    $pdo->prepare("DELETE FROM cart_items WHERE cart_id=?")->execute([$cartId]);
    $pdo->prepare("UPDATE carts SET sub_total=0, delivery_fee=0, total=0, updated_at=NOW() WHERE cart_id=?")->execute([$cartId]);
    echo json_encode([
        'success' => true,
        'cart_id' => $cartId,
        'totals' => ['subtotal' => 0, 'delivery_fee' => 0, 'total' => 0]
    ]);
    exit;
}

// Transactional upsert
$pdo->beginTransaction();
foreach ($cart as $item) {
    if (!isset($item['product_id'], $item['quantity'])) continue;

    $pid = (int)$item['product_id'];
    $qty = max(1, (int)$item['quantity']);
    $size = $item['size'] ?? null;

    $flavorIdsCsv = null;
    if (!empty($item['flavor_ids']) && is_array($item['flavor_ids'])) {
        $flavorIdsCsv = implode(',', $item['flavor_ids']); // store all selected flavors
    }

    // Check if the same product + size + flavor combination exists
    $check = $pdo->prepare("
        SELECT cart_item_id 
        FROM cart_items 
        WHERE cart_id=? AND product_id=? AND (size <=> ?) AND (flavor_ids <=> ?) 
        LIMIT 1
    ");
    $check->execute([$cartId, $pid, $size, $flavorIdsCsv]);
    $row = $check->fetch();

    if ($row) {
        $upd = $pdo->prepare("UPDATE cart_items SET quantity=?, updated_at=NOW() WHERE cart_item_id=?");
        $upd->execute([$qty, $row['cart_item_id']]);
    } else {
        $ins = $pdo->prepare("
            INSERT INTO cart_items (cart_id, product_id, quantity, size, flavor_ids, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $ins->execute([$cartId, $pid, $qty, $size, $flavorIdsCsv]);
    }
}
$pdo->commit();

// Recalculate totals
$stmt = $pdo->prepare("
    SELECT ci.quantity, ci.size, p.product_price, p.price_large
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.product_id
    WHERE ci.cart_id=?
");
$stmt->execute([$cartId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$subtotal = 0;
foreach ($items as $i) {
    $priceItem = ($i['size'] === 'large') ? $i['price_large'] : $i['product_price'];
    $subtotal += $priceItem * $i['quantity'];
}

$deliveryFee = 50;
$total = $subtotal + $deliveryFee;

$stmt = $pdo->prepare("UPDATE carts SET sub_total=?, delivery_fee=?, total=?, updated_at=NOW() WHERE cart_id=?");
$stmt->execute([$subtotal, $deliveryFee, $total, $cartId]);

echo json_encode([
    'success' => true,
    'cart_id' => $cartId,
    'totals' => [
        'subtotal' => (float)$subtotal,
        'delivery_fee' => (float)$deliveryFee,
        'total' => (float)$total
    ]
]);
