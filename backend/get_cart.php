<?php
session_start();
require_once './db_script/init.php';

$sessionId  = session_id();
$userId     = $_SESSION['user_id'] ?? null;
$guestToken = $_COOKIE['guest_token'] ?? null;

if ($userId) {
    // Logged in → always fetch by user_id
    $sql = "SELECT cart_id, sub_total, delivery_fee, total 
            FROM carts 
            WHERE user_id = :uid 
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['uid' => $userId]);
} else {
    // Guest → check guest_token first, fallback to session_id
    $sql = "SELECT cart_id, sub_total, delivery_fee, total 
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

$cartRow = $stmt->fetch();

if (!$cartRow) {
    echo json_encode([
        'success' => true,
        'cart' => [],
        'totals' => [
            'subtotal'     => 0,
            'delivery_fee' => 50, // keep consistent with your default
            'total'        => 50
        ]
    ]);
    exit;
}

$cartId = $cartRow['cart_id'];

// Fetch cart items with product details
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

// Enrich each item with flavor names + computed final price
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

    // Calculate final price (based on size)
    $item['final_price'] = ($item['size'] === 'large') 
        ? (float)$item['price_large'] 
        : (float)$item['product_price'];
}

echo json_encode([
    'success' => true,
    'cart' => $items,
    'totals' => [
        'subtotal'     => (float)$cartRow['sub_total'],
        'delivery_fee' => (float)$cartRow['delivery_fee'],
        'total'        => (float)$cartRow['total']
    ]
]);
