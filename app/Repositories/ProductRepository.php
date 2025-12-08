<?php

namespace App\Repositories;

use App\Config\Database;
use PDO;

class ProductRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function findProductById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.product_name, p.product_price, p.price_large, p.product_picture, c.main_category_id
            FROM products p
            JOIN categories c ON p.category_id = c.category_id
            WHERE p.product_id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getFlavorNamesByIds(array $flavorIds): array
    {
        if (empty($flavorIds)) {
            return [];
        }
        
        $in = str_repeat('?,', count($flavorIds) - 1) . '?';
        $stmt = $this->pdo->prepare("SELECT flavor_name FROM product_flavors WHERE flavor_id IN ($in)");
        $stmt->execute($flavorIds);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
