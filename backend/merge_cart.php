<?php
function mergeGuestCartIntoUser($pdo, $userId, $guestToken, $sessionId) {
    // 1. Look for a guest cart
    $stmt = $pdo->prepare("
        SELECT cart_id FROM carts
        WHERE guest_token = :gtoken OR session_id = :sid
        LIMIT 1
    ");
    $stmt->execute(['gtoken' => $guestToken, 'sid' => $sessionId]);
    $guestCart = $stmt->fetch();

    if (!$guestCart) return;

    // 2. Look for an existing user cart
    $stmt = $pdo->prepare("SELECT cart_id FROM carts WHERE user_id = :uid LIMIT 1");
    $stmt->execute(['uid' => $userId]);
    $userCart = $stmt->fetch();

    if ($userCart) {
        // ✅ Merge guest items into user cart
        $mergeStmt = $pdo->prepare("
            UPDATE cart_items SET cart_id = :ucart WHERE cart_id = :gcart
        ");
        $mergeStmt->execute([
            'ucart' => $userCart['cart_id'],
            'gcart' => $guestCart['cart_id']
        ]);

        // Remove the old guest cart
        $pdo->prepare("DELETE FROM carts WHERE cart_id = ?")->execute([$guestCart['cart_id']]);
    } else {
        // ✅ Just reassign guest cart to this user
        $pdo->prepare("
            UPDATE carts
            SET user_id = :uid, guest_token = NULL, session_id = NULL
            WHERE cart_id = :cid
        ")->execute([
            'uid' => $userId,
            'cid' => $guestCart['cart_id']
        ]);
    }
}
