<?php
session_start();
require_once '../backend/db_script/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false, 'message'=>'Invalid request']);
    exit;
}

$productId = $_POST['product_id'] ?? null;
$quantity  = max(1, intval($_POST['quantity'] ?? 1));
$size      = $_POST['size'] ?? null;
$flavorIds = json_decode($_POST['flavors'] ?? '[]', true);

if (!$productId) {
    echo json_encode(['success'=>false, 'message'=>'No product ID']);
    exit;
}

$sessionId = session_id();
$userId = $_SESSION['user_id'] ?? null;

// Fetch product info
$stmt = $pdo->prepare("
    SELECT p.product_name, p.product_price, p.price_large, p.product_picture, c.main_category_id
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    WHERE p.product_id = :id
");
$stmt->execute(['id'=>$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo json_encode(['success'=>false, 'message'=>'Product not found']);
    exit;
}

// Only drinks have size
if ((int)$product['main_category_id'] !== 2) $size = null;

// Prepare flavor CSV & names
$flavorIdsCsv = null;
$flavorNames = '';
if (!empty($flavorIds)) {
    $flavorIds = array_map('intval', $flavorIds);
    if (count($flavorIds) > 3) {
        echo json_encode(['success'=>false,'message'=>'Select up to 3 flavors']);
        exit;
    }
    $flavorIdsCsv = implode(',', $flavorIds);

    $in = str_repeat('?,', count($flavorIds)-1) . '?';
    $fstmt = $pdo->prepare("SELECT flavor_name FROM product_flavors WHERE flavor_id IN ($in)");
    $fstmt->execute($flavorIds);
    $flavorNames = implode(', ', $fstmt->fetchAll(PDO::FETCH_COLUMN));
}

// --- Handle guest_token for persistent carts ---
if (!$userId) {
    if (empty($_COOKIE['guest_token'])) {
        $guestToken = bin2hex(random_bytes(16));
        setcookie("guest_token", $guestToken, time() + (86400*30), "/"); // 30 days
    } else {
        $guestToken = $_COOKIE['guest_token'];
    }
} else {
    $guestToken = null;
}

// --- Find or create cart ---
if ($userId) {
    // Logged in: fetch by user_id
    $stmt = $pdo->prepare("SELECT cart_id FROM carts WHERE user_id=:uid LIMIT 1");
    $stmt->execute(['uid'=>$userId]);
} else {
    // Guest: fetch by session_id or guest_token
    $stmt = $pdo->prepare("SELECT cart_id FROM carts WHERE session_id=:sid OR guest_token=:gtoken LIMIT 1");
    $stmt->execute(['sid'=>$sessionId, 'gtoken'=>$guestToken]);
}
$cartRow = $stmt->fetch();
$cartId = $cartRow ? $cartRow['cart_id'] : null;

if (!$cartId) {
    $stmt = $pdo->prepare("INSERT INTO carts (user_id, session_id, guest_token, option_type, sub_total, delivery_fee, total, created_at, updated_at)
                           VALUES (:uid, :sid, :gtoken, 'delivery', 0, 50, 50, NOW(), NOW())");
    $stmt->execute([
        'uid'    => $userId,
        'sid'    => $sessionId,
        'gtoken' => $guestToken
    ]);
    $cartId = $pdo->lastInsertId();
}


// Check if same item exists (same product, size, and exact flavor combination)
$check = $pdo->prepare("
    SELECT cart_item_id, quantity 
    FROM cart_items 
    WHERE cart_id=:cart_id 
      AND product_id=:pid 
      AND (size <=> :size) 
      AND (flavor_ids <=> :flavor_ids)
");
$check->execute([
    'cart_id'=>$cartId,
    'pid'=>$productId,
    'size'=>$size,
    'flavor_ids'=>$flavorIdsCsv
]);
$existing = $check->fetch();

if ($existing) {
    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = quantity + :qty, updated_at=NOW() WHERE cart_item_id=:id");
    $stmt->execute(['qty'=>$quantity,'id'=>$existing['cart_item_id']]);
} else {
    $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, size, flavor_ids, created_at, updated_at)
                           VALUES (:cart_id,:pid,:qty,:size,:flavor_ids,NOW(),NOW())");
    $stmt->execute([
        'cart_id'=>$cartId,
        'pid'=>$productId,
        'qty'=>$quantity,
        'size'=>$size,
        'flavor_ids'=>$flavorIdsCsv
    ]);
}

// Recalculate totals
$stmt = $pdo->prepare("
    SELECT ci.quantity, ci.size, p.product_price, p.price_large
    FROM cart_items ci
    JOIN products p ON ci.product_id=p.product_id
    WHERE ci.cart_id=:cart_id
");
$stmt->execute(['cart_id'=>$cartId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$subtotal = 0;
foreach ($items as $i) {
    $itemPrice = ($i['size']==='large') ? $i['price_large'] : $i['product_price'];
    $subtotal += $itemPrice * $i['quantity'];
}
$deliveryFee = 50;
$total = $subtotal + $deliveryFee;

$stmt = $pdo->prepare("UPDATE carts SET sub_total=:sub, delivery_fee=:fee, total=:total, updated_at=NOW() WHERE cart_id=:id");
$stmt->execute(['sub'=>$subtotal,'fee'=>$deliveryFee,'total'=>$total,'id'=>$cartId]);

echo json_encode([
    'success'=>true,
    'cart_id'=>$cartId,
    'sub_total'=>$subtotal,
    'delivery_fee'=>$deliveryFee,
    'total'=>$total,
    'flavor_names'=>$flavorNames
]);
