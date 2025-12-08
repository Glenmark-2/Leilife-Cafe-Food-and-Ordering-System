<?php

namespace App\Repositories;

use App\Config\Database;
use PDO;

class UserRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function getUserProfile(int $userId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT user_id, first_name, last_name, phone_number, email
            FROM users
            WHERE user_id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function updatePhoneNumber(int $userId, string $phone): void
    {
        $stmt = $this->pdo->prepare("UPDATE users SET phone_number = :phone WHERE user_id = :id");
        $stmt->execute([':phone' => $phone, ':id' => $userId]);
    }
    
    // For joining User + Address like in key parts of checkout
    public function getUserWithAddress(int $userId): ?array
    {
         $stmt = $this->pdo->prepare("
            SELECT u.first_name, u.last_name, u.phone_number,
                   a.street_address, a.barangay, a.city_name, a.region_name,
                   a.province_name, a.note_to_rider, a.city, a.region, a.province
            FROM users u
            LEFT JOIN addresses a ON a.user_id = u.user_id
            WHERE u.user_id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
