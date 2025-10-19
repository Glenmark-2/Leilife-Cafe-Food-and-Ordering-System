<?php
session_start();
require_once './db_script/init.php';

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!is_array($data) || empty($data['action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$action     = $data['action'];
$sessionId  = session_id();
$userId     = $_SESSION['user_id'] ?? null;
$guestToken = $_COOKIE['guest_token'] ?? null;

// --- Find or create cart ---
if ($userId) {
    $sql = "SELECT cart_id FROM carts WHERE user_id = :uid LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['uid' => $userId]);
} else {
    $sql = "SELECT cart_id FROM carts WHERE guest_token = :gtoken OR session_id = :sid LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['gtoken' => $guestToken, 'sid' => $sessionId]);
}
$cartRow = $stmt->fetch(PDO::FETCH_ASSOC);

if ($cartRow) {
    $cartId = $cartRow['cart_id'];
} else {
    $stmt = $pdo->prepare("
        INSERT INTO carts (session_id, user_id, guest_token, option_type, sub_total, delivery_fee, total, created_at, updated_at)
        VALUES (:sid, :uid, :gtoken, 'delivery', 0, 0, 0, NOW(), NOW())
    ");
    $stmt->execute([
        'sid'    => $sessionId,
        'uid'    => $userId,
        'gtoken' => $guestToken
    ]);
    $cartId = $pdo->lastInsertId();
}

// --- Perform action ---
try {
    if ($action === "update" && isset($data['cart_item_id'], $data['quantity'])) {
        $qty = max(1, (int)$data['quantity']);
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity=?, updated_at=NOW() WHERE cart_item_id=? AND cart_id=?");
        $stmt->execute([$qty, $data['cart_item_id'], $cartId]);

    } elseif ($action === "remove" && isset($data['cart_item_id'])) {
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_item_id=? AND cart_id=?");
        $stmt->execute([$data['cart_item_id'], $cartId]);

    } elseif ($action === "add" && isset($data['product_id'], $data['quantity'])) {
        $pid = (int)$data['product_id'];
        $qty = max(1, (int)$data['quantity']);
        $size = $data['size'] ?? null;

        $flavorIdsCsv = null;
        if (!empty($data['flavor_ids']) && is_array($data['flavor_ids'])) {
            sort($data['flavor_ids']);
            $flavorIdsCsv = implode(',', $data['flavor_ids']);
        }

        // check if product already exists in cart with same options
        $check = $pdo->prepare("
            SELECT cart_item_id FROM cart_items
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

    // --- Recalculate totals ---
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

    // ✅ Delivery fee = 0 if cart empty
    $deliveryFee = ($subtotal > 0) ? 50 : 0;
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
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Cart update failed', 'error' => $e->getMessage()]);
}
