<?php

namespace App\Repositories;

use App\Config\Database;
use PDO;

class OrderRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function beginTransaction(): void
    {
        if (!$this->pdo->inTransaction()) {
            $this->pdo->beginTransaction();
        }
    }

    public function commit(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->commit();
        }
    }

    public function rollBack(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    public function createOrder(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO orders (user_id, total, payment_method, payment_status, order_number, delivery_method, status)
            VALUES (:uid, :total, :payment, :status, :order_number, :delivery_method, 'pending')
        ");
        $stmt->execute([
            ':uid' => $data['user_id'],
            ':total' => $data['total'],
            ':payment' => $data['payment_method'],
            ':status' => 'unpaid',
            ':order_number' => $data['order_number'],
            ':delivery_method' => $data['delivery_method']
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function createOrderItem(int $orderId, array $item): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price, size, flavor_ids)
            VALUES (:order_id, :product_id, :quantity, :price, :size, :flavor_ids)
        ");
        $stmt->execute([
            ':order_id' => $orderId,
            ':product_id' => $item['product_id'],
            ':quantity' => $item['quantity'],
            ':price' => $item['price'],
            ':size' => $item['size'] ?? null,
            ':flavor_ids' => $item['flavor_ids'] ?? null
        ]);
    }

    public function getOrderNumberById(int $orderId): ?string
    {
        $stmt = $this->pdo->prepare("SELECT order_number FROM orders WHERE order_id = :id");
        $stmt->execute(['id' => $orderId]);
        return $stmt->fetchColumn() ?: null;
    }
    
    // For validation or other lookups
    public function findOrderByNumber(string $orderNumber): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE order_number = :num LIMIT 1");
        $stmt->execute(['num' => $orderNumber]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    public function findOrderById(int $orderId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE order_id = :id LIMIT 1");
        $stmt->execute(['id' => $orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function updateOrderStatus(int $orderId, string $status): void
    {
        $stmt = $this->pdo->prepare("UPDATE orders SET status = :status WHERE order_id = :id");
        $stmt->execute(['status' => $status, 'id' => $orderId]);
    }

    public function updateAllOrderItemsStatus(int $orderId, string $status): void
    {
        $stmt = $this->pdo->prepare("UPDATE order_items SET status = :status WHERE order_id = :id");
        $stmt->execute(['status' => $status, 'id' => $orderId]);
    }
    
    public function getOrderSubtotalExcludingCancelled(int $orderId): float
    {
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(price * quantity), 0) 
            FROM order_items 
            WHERE order_id = :id AND status != 'cancelled'
        ");
        $stmt->execute(['id' => $orderId]);
        return (float)$stmt->fetchColumn();
    }

    public function updateOrderTotal(int $orderId, float $total): void
    {
        $stmt = $this->pdo->prepare("UPDATE orders SET total = :total WHERE order_id = :id");
        $stmt->execute(['total' => $total, 'id' => $orderId]);
    }

    public function markRefundRequested(int $orderId): void
    {
        $stmt = $this->pdo->prepare("UPDATE orders SET refund_requested_at = NOW() WHERE order_id = :id");
        $stmt->execute(['id' => $orderId]);
    }
    
    public function getSuccessfulPaymentTransaction(int $orderId): ?string
    {
        $stmt = $this->pdo->prepare("
            SELECT external_id 
            FROM transactions 
            WHERE order_id = :id 
              AND transaction_name = 'payment' 
              AND transaction_status = 'success' 
            ORDER BY transaction_id DESC 
            LIMIT 1
        ");
        $stmt->execute(['id' => $orderId]);
        return $stmt->fetchColumn() ?: null;
    }
}
