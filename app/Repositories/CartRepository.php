<?php

namespace App\Repositories;

use App\Config\Database;
use PDO;
use PDOException;

class CartRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function findCartByUserId(int $userId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT cart_id, option_type, sub_total, delivery_fee, total FROM carts WHERE user_id = :uid LIMIT 1");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findCartBySessionOrToken(string $sessionId, ?string $guestToken): ?array
    {
        $stmt = $this->pdo->prepare("SELECT cart_id, option_type, sub_total, delivery_fee, total FROM carts WHERE session_id = :sid OR guest_token = :gtoken LIMIT 1");
        $stmt->execute(['sid' => $sessionId, 'gtoken' => $guestToken]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function createCart(?int $userId, string $sessionId, ?string $guestToken): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO carts (user_id, session_id, guest_token, option_type, sub_total, delivery_fee, total, created_at, updated_at)
            VALUES (:uid, :sid, :gtoken, 'delivery', 0, 0, 0, NOW(), NOW())
        ");
        $stmt->execute([
            'uid'    => $userId,
            'sid'    => $sessionId,
            'gtoken' => $guestToken
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function updateCartOptionType(int $cartId, string $payload): void
    {
        $stmt = $this->pdo->prepare("UPDATE carts SET option_type = :type, updated_at = NOW() WHERE cart_id = :id");
        $stmt->execute(['type' => $payload, 'id' => $cartId]);
    }

    public function findCartItem(int $cartId, int $productId, ?string $size, ?string $flavorIdsCsv): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT cart_item_id, quantity 
            FROM cart_items 
            WHERE cart_id=:cart_id 
              AND product_id=:pid 
              AND (size <=> :size) 
              AND (flavor_ids <=> :flavor_ids)
        ");
        $stmt->execute([
            'cart_id' => $cartId,
            'pid' => $productId,
            'size' => $size,
            'flavor_ids' => $flavorIdsCsv
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function addCartItem(int $cartId, int $productId, int $quantity, ?string $size, ?string $flavorIdsCsv): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, size, flavor_ids, created_at, updated_at)
                               VALUES (:cart_id, :pid, :qty, :size, :flavor_ids, NOW(), NOW())");
        $stmt->execute([
            'cart_id' => $cartId,
            'pid' => $productId,
            'qty' => $quantity,
            'size' => $size,
            'flavor_ids' => $flavorIdsCsv
        ]);
    }

    public function updateCartItemQuantity(int $itemId, int $quantity): void
    {
        $stmt = $this->pdo->prepare("UPDATE cart_items SET quantity = :qty, updated_at = NOW() WHERE cart_item_id = :id");
        $stmt->execute(['qty' => $quantity, 'id' => $itemId]);
    }

    public function incrementCartItemQuantity(int $itemId, int $quantity): void
    {
        $stmt = $this->pdo->prepare("UPDATE cart_items SET quantity = quantity + :qty, updated_at = NOW() WHERE cart_item_id = :id");
        $stmt->execute(['qty' => $quantity, 'id' => $itemId]);
    }

    public function removeCartItem(int $itemId, int $cartId): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM cart_items WHERE cart_item_id = :itemId AND cart_id = :cartId");
        $stmt->execute(['itemId' => $itemId, 'cartId' => $cartId]);
    }

    public function getCartItems(int $cartId): array
    {
        $stmt = $this->pdo->prepare("
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
            WHERE ci.cart_id = :cart_id
        ");
        $stmt->execute(['cart_id' => $cartId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findCartById(int $cartId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT cart_id, option_type, sub_total, delivery_fee, total, user_id FROM carts WHERE cart_id = :id LIMIT 1");
        $stmt->execute(['id' => $cartId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function countItems(int $cartId): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM cart_items WHERE cart_id = :cid");
        $stmt->execute(['cid' => $cartId]);
        return (int)$stmt->fetchColumn();
    }
    
    public function removeCartItemsAll(int $cartId): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM cart_items WHERE cart_id = :cid");
        $stmt->execute(['cid' => $cartId]);
    }

    public function updateCartTotals(int $cartId, float $sub, float $delivery, float $total): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE carts 
            SET sub_total = :sub, delivery_fee = :delivery, total = :total, updated_at = NOW() 
            WHERE cart_id = :id
        ");
        $stmt->execute(['sub' => $sub, 'delivery' => $delivery, 'total' => $total, 'id' => $cartId]);
    }

    public function getCustomerCoordinates(int $userId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT latitude, longitude 
            FROM addresses
            WHERE user_id = :uid
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute(['uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && $row['latitude'] !== null && $row['longitude'] !== null) {
            return [
                'lat' => (float)$row['latitude'],
                'lng' => (float)$row['longitude']
            ];
        }
        return null;
    }


    public function recalcCartTotals(int $cartId): void
    {
        $sumSql = "
            SELECT COALESCE(SUM(
                (CASE
                    WHEN ci.size = 'large' THEN COALESCE(p.price_large, p.product_price)
                    ELSE p.product_price
                END) * ci.quantity
            ), 0) AS sub_total
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.product_id
            WHERE ci.cart_id = :cid
        ";
        $stmt = $this->pdo->prepare($sumSql);
        $stmt->execute([':cid' => $cartId]);
        $sub = (float)$stmt->fetchColumn();

        $delivery = 0.00; // Recalculation logic for delivery fee should be dynamic in service, here fixed or passed?
        // Ideally recalc should just recalc subtotal, and let Service handle Delivery fee logic which depends on Address.
        // But for now, let's keep it simple or minimal.
        
        // However, we are inside Repository. Repository shouldn't decide business logic for delivery fee.
        // But the previous implementation had it here? 
        // Actually, CartService::recalculateAndReturn calls CartRepository::updateCartTotals after calculating.
        // This recalcCartTotals seems to be a self-contained helper. 
        // Let's leave it as is for now but implement mergeCarts.
        
        $total = round($sub + $delivery, 2);

        $upd = $this->pdo->prepare("
            UPDATE carts 
            SET sub_total = :sub, delivery_fee = :delivery, total = :total, updated_at = NOW() 
            WHERE cart_id = :cid
        ");
        $upd->execute([
            ':sub'      => $sub,
            ':delivery' => $delivery,
            ':total'    => $total,
            ':cid'      => $cartId
        ]);
    }

    public function mergeCarts(int $userId, ?string $guestToken, string $sessionId): void
    {
        // 1. Find Guest Cart
        $stmt = $this->pdo->prepare("
            SELECT cart_id FROM carts
            WHERE guest_token = :gtoken OR session_id = :sid
            LIMIT 1
        ");
        $stmt->execute(['gtoken' => $guestToken, 'sid' => $sessionId]);
        $guestCart = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$guestCart) {
            return;
        }

        // 2. Find User Cart
        $stmt = $this->pdo->prepare("SELECT cart_id FROM carts WHERE user_id = :uid LIMIT 1");
        $stmt->execute(['uid' => $userId]);
        $userCart = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userCart) {
            // MERGE: Move items from guest cart to user cart
            $mergeStmt = $this->pdo->prepare("
                UPDATE cart_items SET cart_id = :ucart, updated_at = NOW() WHERE cart_id = :gcart
            ");
            $mergeStmt->execute([
                'ucart' => $userCart['cart_id'],
                'gcart' => $guestCart['cart_id']
            ]);

            // Delete guest cart
            $this->pdo->prepare("DELETE FROM carts WHERE cart_id = :cid")->execute(['cid' => $guestCart['cart_id']]);
            
            // Recalculate User Cart Totals
            $this->recalcCartTotals((int)$userCart['cart_id']);
        } else {
            // REASSIGN: Guest cart becomes user cart
            $this->pdo->prepare("
                UPDATE carts
                SET user_id = :uid, guest_token = NULL, session_id = NULL, updated_at = NOW()
                WHERE cart_id = :cid
            ")->execute([
                'uid' => $userId,
                'cid' => $guestCart['cart_id']
            ]);
        }
    }
}
