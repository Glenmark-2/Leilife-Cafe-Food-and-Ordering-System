<?php
session_start();
require_once '../backend/db_script/db.php';

// ===============================
// Haversine distance (km)
// ===============================
function getDistanceKm($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // kilometers
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);

    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earthRadius * $c;
}

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

// ================================
// ✅ HARDCODE STORE COORDS (change to your store)
// ================================
$storeLat = 14.6543;    // <-- update if needed
$storeLng = 120.9721;   // <-- update if needed

// ================================
// ✅ GET CUSTOMER COORDS FROM addresses TABLE
// We'll pick the most recent address row for the user (delivery addresses).
// ================================
$customerLat = null;
$customerLng = null;

if ($userId) {
    // Try to find the most-recent delivery address with coordinates
    $addrStmt = $pdo->prepare("
        SELECT latitude, longitude, delivery_option
        FROM addresses
        WHERE user_id = :uid
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $addrStmt->execute(['uid' => $userId]);
    $addrRow = $addrStmt->fetch(PDO::FETCH_ASSOC);

    if ($addrRow) {
        // Only accept valid numeric lat/lng
        $lat = $addrRow['latitude'];
        $lng = $addrRow['longitude'];
        if ($lat !== null && $lng !== null && $lat !== '' && $lng !== '') {
            // cast safely to float
            $customerLat = floatval($lat);
            $customerLng = floatval($lng);
        }
    }
}

// -----------------------------
// Fetch product info
// -----------------------------
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
if (!empty($flavorIds) && is_array($flavorIds)) {
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
    $stmt = $pdo->prepare("SELECT cart_id, option_type FROM carts WHERE user_id=:uid LIMIT 1");
    $stmt->execute(['uid'=>$userId]);
} else {
    $stmt = $pdo->prepare("SELECT cart_id, option_type FROM carts WHERE session_id=:sid OR guest_token=:gtoken LIMIT 1");
    $stmt->execute(['sid'=>$sessionId, 'gtoken'=>$guestToken]);
}
$cartRow = $stmt->fetch(PDO::FETCH_ASSOC);
$cartId = $cartRow['cart_id'] ?? null;
$optionType = $cartRow['option_type'] ?? 'delivery';

if (!$cartId) {
    $stmt = $pdo->prepare("
        INSERT INTO carts (user_id, session_id, guest_token, option_type, sub_total, delivery_fee, total, created_at, updated_at)
        VALUES (:uid, :sid, :gtoken, 'delivery', 0, 0, 0, NOW(), NOW())
    ");
    $stmt->execute([
        'uid'    => $userId,
        'sid'    => $sessionId,
        'gtoken' => $guestToken
    ]);
    $cartId = $pdo->lastInsertId();
    $optionType = 'delivery';
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

// --- Recalculate totals ---
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

/* ======================================================
   DYNAMIC DELIVERY FEE
   Base fare: ₱10
   + ₱10 for every succeeding 1 km (ceil)
   ===================================================== */
$deliveryFee = 0;

if ($optionType !== 'pickup') {
    if ($customerLat !== null && $customerLng !== null) {
        $km = getDistanceKm($storeLat, $storeLng, $customerLat, $customerLng);
        // If you intend "within 1 km = base only", you might want to subtract 1 before ceil.
        // Current interpretation: base ₱10 + ₱10 * ceil(km)
        $deliveryFee = 10 + (ceil($km) * 10);
    } else {
        // No customer coords found — fallback to base fee
        $deliveryFee = 10;
    }
}

$total = $subtotal + $deliveryFee;

// --- Update cart totals ---
$stmt = $pdo->prepare("
    UPDATE carts 
    SET sub_total=:sub, delivery_fee=:fee, total=:total, updated_at=NOW() 
    WHERE cart_id=:id
");
$stmt->execute(['sub'=>$subtotal,'fee'=>$deliveryFee,'total'=>$total,'id'=>$cartId]);

echo json_encode([
    'success' => true,
    'cart_id' => $cartId,
    'option_type' => $optionType,
    'sub_total' => $subtotal,
    'delivery_fee' => $deliveryFee,
    'total' => $total,
    'flavor_names' => $flavorNames
]);
