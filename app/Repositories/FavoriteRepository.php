<?php

namespace App\Repositories;

use App\Config\Database;
use PDO;

class FavoriteRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function findFavorite(int $userId, int $productId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT favorite_id FROM favorites WHERE user_id = :user AND product_id = :product LIMIT 1");
        $stmt->execute(['user' => $userId, 'product' => $productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function addFavorite(int $userId, int $productId): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO favorites (user_id, product_id) VALUES (:user, :product)");
        $stmt->execute(['user' => $userId, 'product' => $productId]);
    }

    public function removeFavorite(int $favoriteId): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM favorites WHERE favorite_id = :id");
        $stmt->execute(['id' => $favoriteId]);
    }
}
