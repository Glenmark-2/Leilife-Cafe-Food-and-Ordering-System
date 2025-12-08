<?php

namespace App\Repositories;

use App\Config\Database;
use PDO;

class AddressRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function getAddressByUserId(int $userId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT street_address, barangay, city, region, province, note_to_rider
            FROM addresses
            WHERE user_id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function updateAddress(int $userId, array $data): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE addresses
            SET street_address = :street,
                barangay = :barangay,
                city = :city,
                region = :region,
                province = :province,
                note_to_rider = :note
            WHERE user_id = :id
        ");
        $stmt->execute([
            ':street'   => $data['street_address'],
            ':barangay' => $data['barangay'],
            ':city'     => $data['city'],
            ':region'   => $data['region'],
            ':province' => $data['province'],
            ':note'     => $data['note_to_rider'],
            ':id'       => $userId
        ]);

        return $stmt->rowCount() > 0;
    }

    public function createAddress(int $userId, array $data): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO addresses (user_id, street_address, barangay, city, region, province, note_to_rider)
            VALUES (:id, :street, :barangay, :city, :region, :province, :note)
        ");
        $stmt->execute([
            ':id'       => $userId,
            ':street'   => $data['street_address'],
            ':barangay' => $data['barangay'],
            ':city'     => $data['city'],
            ':region'   => $data['region'],
            ':province' => $data['province'],
            ':note'     => $data['note_to_rider']
        ]);
    }
}
