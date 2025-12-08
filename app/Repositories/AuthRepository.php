<?php

namespace App\Repositories;

use App\Config\Database;
use PDO;

class AuthRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function findAdminByLogin(string $input): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM admin_accounts WHERE email = :input OR username = :input LIMIT 1");
        $stmt->execute(['input' => $input]);
        return $stmt->fetch() ?: null;
    }

    public function findDriverByLogin(string $input): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM driver_accounts WHERE email = :input OR username = :input LIMIT 1");
        $stmt->execute(['input' => $input]);
        return $stmt->fetch() ?: null;
    }

    public function findUserByLogin(string $input): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT user_id, username, email, password_hash, auth_provider, profile_picture
            FROM users 
            WHERE email = :input OR username = :input
            LIMIT 1
        ");
        $stmt->execute(['input' => $input]);
        return $stmt->fetch() ?: null;
    }

    public function findUserByEmail(string $email): ?array
    {
         $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
         $stmt->execute([':email' => $email]);
         return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function updateProfilePicture(int $userId, string $url): void
    {
        $stmt = $this->pdo->prepare("UPDATE users SET profile_picture = :pic WHERE user_id = :id");
        $stmt->execute([':pic' => $url, ':id' => $userId]);
    }

    public function createGoogleUser(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (username, first_name, last_name, email, password_hash, auth_provider, google_id, profile_picture) 
            VALUES (:username, :first_name, :last_name, :email, NULL, 'google', :google_id, :profile_picture)
        ");
        $stmt->execute([
            ':username'   => $data['username'],
            ':first_name' => $data['first_name'],
            ':last_name'  => $data['last_name'],
            ':email'      => $data['email'],
            ':google_id'  => $data['google_id'],
            ':profile_picture' => $data['profile_picture']
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function isEmailRegistered(string $email): bool
    {
        $tables = ['users', 'admin_accounts', 'driver_accounts'];
        
        foreach ($tables as $table) {
             $stmt = $this->pdo->prepare("SELECT 1 FROM {$table} WHERE email = :email LIMIT 1");
             $stmt->execute([':email' => $email]);
             if ($stmt->fetchColumn()) {
                 return true;
             }
        }
        return false;
    }
}
