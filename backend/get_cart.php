<?php
session_start();
require_once './db_script/init.php';

$sessionId  = session_id();
$userId     = $_SESSION['user_id'] ?? null;
$guestToken = $_COOKIE['guest_token'] ?? null;

// --- Get cart row ---
if ($userId) {
    $sql = "SELECT cart_id, sub_total, delivery_fee, total, option_type
            FROM carts 
            WHERE user_id = :uid 
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['uid' => $userId]);
} else {
    $sql = "SELECT cart_id, sub_total, delivery_fee, total, option_type
            FROM carts 
            WHERE guest_token = :gtoken 
               OR session_id = :sid 
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'gtoken' => $guestToken,
        'sid'    => $sessionId
    ]);
}

$cartRow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cartRow) {
    // 👇 Empty cart response (no row found at all)
    echo json_encode([
        'success' => true,
        'cart' => [],
        'option_type' => 'delivery', // default
        'totals' => [
            'subtotal'     => 0,
            'delivery_fee' => 0,
            'total'        => 0
        ]
    ]);
    exit;
}

$cartId = $cartRow['cart_id'];
$optionType = strtolower($cartRow['option_type'] ?? 'delivery');

// --- Fetch items ---
$stmt = $pdo->prepare("
    SELECT 
        ci.cart_item_id,
        ci.product_id,
        ci.quantity,
        ci.size,
        ci.flavor_ids,
        p.product_name,
        p.product_picture,
        p.product_price,
        p.price_large
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.product_id
    WHERE ci.cart_id = ?
");
$stmt->execute([$cartId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$subtotal = 0;

foreach ($items as &$item) {
    $item['flavor_names'] = '';

    if (!empty($item['flavor_ids'])) {
        $flavorIds = explode(',', $item['flavor_ids']);
        $in = str_repeat('?,', count($flavorIds) - 1) . '?';
        $fstmt = $pdo->prepare("SELECT flavor_name FROM product_flavors WHERE flavor_id IN ($in)");
        $fstmt->execute($flavorIds);
        $names = $fstmt->fetchAll(PDO::FETCH_COLUMN);
        $item['flavor_names'] = implode(', ', $names);
    }

    // Final price depends on size
    $item['final_price'] = ($item['size'] === 'large')
        ? (float)$item['price_large']
        : (float)$item['product_price'];

    // Add to subtotal
    $subtotal += $item['final_price'] * $item['quantity'];
}

// ✅ Apply delivery fee only if mode = delivery
if ($optionType === 'delivery') {
    $deliveryFee = $subtotal > 0 ? (float)($cartRow['delivery_fee'] ?? 50) : 0;
} else {
    $deliveryFee = 0;
}

$total = $subtotal + $deliveryFee;

// --- Sync totals back to DB ---
$upd = $pdo->prepare("
    UPDATE carts 
    SET sub_total=?, delivery_fee=?, total=?, option_type=?, updated_at=NOW() 
    WHERE cart_id=?
");
$upd->execute([$subtotal, $deliveryFee, $total, $optionType, $cartId]);

echo json_encode([
    'success' => true,
    'cart' => $items,
    'option_type' => $cartRow['option_type'] ?? 'delivery', // ✅ include this
    'totals' => [
        'subtotal'     => $subtotal,
        'delivery_fee' => $deliveryFee,
        'total'        => $total
    ]
]);

