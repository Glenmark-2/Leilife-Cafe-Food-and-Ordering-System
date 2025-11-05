<?php
session_start();
require_once './db_script/init.php';

header('Content-Type: application/json');

/* ======================================================
   Haversine Distance (km)
====================================================== */
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

/* ======================================================
   Fixed Store Coordinates (change if needed)
====================================================== */
$storeLat = 14.6543;
$storeLng = 120.9721;

/* ======================================================
   Read Request
====================================================== */
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!is_array($data) || empty($data['action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$action     = $data['action'];
$optionType = strtolower(trim($data['option_type'] ?? ''));
$sessionId  = session_id();
$userId     = $_SESSION['user_id'] ?? null;
$guestToken = $_COOKIE['guest_token'] ?? null;

/* ======================================================
   Get or Create Cart
====================================================== */
if ($userId) {
    $stmt = $pdo->prepare("SELECT cart_id, option_type FROM carts WHERE user_id = ? LIMIT 1");
    $stmt->execute([$userId]);
} else {
    $stmt = $pdo->prepare("SELECT cart_id, option_type FROM carts WHERE guest_token = ? OR session_id = ? LIMIT 1");
    $stmt->execute([$guestToken, $sessionId]);
}
$cartRow = $stmt->fetch(PDO::FETCH_ASSOC);

if ($cartRow) {
    $cartId = $cartRow['cart_id'];
    $currentOptionType = strtolower($cartRow['option_type'] ?? 'delivery');
} else {
    $stmt = $pdo->prepare("
        INSERT INTO carts (session_id, user_id, guest_token, option_type, sub_total, delivery_fee, total, created_at, updated_at)
        VALUES (?, ?, ?, 'delivery', 0, 0, 0, NOW(), NOW())
    ");
    $stmt->execute([$sessionId, $userId, $guestToken]);
    $cartId = $pdo->lastInsertId();
    $currentOptionType = 'delivery';
}

/* ======================================================
   MAIN ACTION
====================================================== */

try {

    /* ✅ UPDATE OPTION TYPE */
    if ($action === 'update_option_type') {
        if (!in_array($optionType, ['delivery', 'pickup'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid option type']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE carts SET option_type=?, updated_at=NOW() WHERE cart_id=?");
        $stmt->execute([$optionType, $cartId]);
        $currentOptionType = $optionType;
    }

    /* ✅ UPDATE QTY */
    if ($action === "update" && isset($data['cart_item_id'], $data['quantity'])) {
        $qty = max(1, (int)$data['quantity']);
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity=?, updated_at=NOW() WHERE cart_item_id=? AND cart_id=?");
        $stmt->execute([$qty, $data['cart_item_id'], $cartId]);
    }

    /* ✅ REMOVE ITEM */
    if ($action === "remove" && isset($data['cart_item_id'])) {
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_item_id=? AND cart_id=?");
        $stmt->execute([$data['cart_item_id'], $cartId]);
    }

    /* ✅ ADD ITEM */
    if ($action === "add" && isset($data['product_id'], $data['quantity'])) {

        $pid = (int)$data['product_id'];
        $qty = max(1, (int)$data['quantity']);
        $size = $data['size'] ?? null;

        $flavorIdsCsv = null;
        if (!empty($data['flavor_ids']) && is_array($data['flavor_ids'])) {
            sort($data['flavor_ids']);
            $flavorIdsCsv = implode(',', $data['flavor_ids']);
        }

        $check = $pdo->prepare("
            SELECT cart_item_id 
            FROM cart_items
            WHERE cart_id=? AND product_id=? AND (size <=> ?) AND (flavor_ids <=> ?)
            LIMIT 1
        ");
        $check->execute([$cartId, $pid, $size, $flavorIdsCsv]);
        $row = $check->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $upd = $pdo->prepare("UPDATE cart_items SET quantity=quantity+?, updated_at=NOW() WHERE cart_item_id=?");
            $upd->execute([$qty, $row['cart_item_id']]);
        } else {
            $ins = $pdo->prepare("
                INSERT INTO cart_items (cart_id, product_id, quantity, size, flavor_ids, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $ins->execute([$cartId, $pid, $qty, $size, $flavorIdsCsv]);
        }
    }

    /* ======================================================
       Recompute subtotal
    ====================================================== */
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

    /* ======================================================
       ✅ GET CUSTOMER COORDS FROM `addresses`
    ====================================================== */
    $customerLat = null;
    $customerLng = null;

    if ($userId) {
        $q = $pdo->prepare("SELECT latitude, longitude FROM addresses WHERE user_id=? ORDER BY created_at DESC LIMIT 1");
        $q->execute([$userId]);
        $a = $q->fetch(PDO::FETCH_ASSOC);

        if ($a && $a['latitude'] !== null && $a['longitude'] !== null) {
            $customerLat = floatval($a['latitude']);
            $customerLng = floatval($a['longitude']);
        }
    }

    /* ======================================================
       ✅ DYNAMIC DELIVERY FEE
       base ₱10 + ₱10 * ceil(distance_km)
    ====================================================== */
    $deliveryFee = 0;

    if ($currentOptionType === "delivery" && $subtotal > 0) {
        if ($customerLat !== null && $customerLng !== null) {

            $km = getDistanceKm($storeLat, $storeLng, $customerLat, $customerLng);
            $deliveryFee = 10 + (ceil($km) * 10);

        } else {
            $deliveryFee = 10; // fallback
        }
    }

    $total = $subtotal + $deliveryFee;

    /* UPDATE CART */
    $stmt = $pdo->prepare("
        UPDATE carts 
        SET sub_total=?, delivery_fee=?, total=?, updated_at=NOW()
        WHERE cart_id=?
    ");
    $stmt->execute([$subtotal, $deliveryFee, $total, $cartId]);

    echo json_encode([
        'success' => true,
        'cart_id' => $cartId,
        'option_type' => $currentOptionType,
        'totals' => [
            'subtotal'     => (float)$subtotal,
            'delivery_fee' => (float)$deliveryFee,
            'total'        => (float)$total
        ]
    ]);

} catch (Exception $e) {

    echo json_encode([
        'success' => false,
        'message' => 'Cart update failed',
        'error' => $e->getMessage()
    ]);
}
