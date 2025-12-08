<?php

namespace App\Repositories;

use App\Config\Database;
use PDO;

class RegistrationRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function findPendingByEmail(string $email): ?array
    {
        // Add locking if needed, but for now simple select
        $stmt = $this->pdo->prepare("SELECT reg_id, verification_sent_at, expires_at FROM user_registrations WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function updateRegistration(int $id, array $data): void
    {
        $sql = "
            UPDATE user_registrations 
            SET verification_token = :token,
                verification_sent_at = :sent_at,
                expires_at = :expires_at,
                password_hash = :password_hash,
                first_name = :fname,
                last_name = :lname,
                phone_number = :phone
            WHERE reg_id = :id
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':token'       => $data['token'],
            ':sent_at'     => $data['sent_at'],
            ':expires_at'  => $data['expires_at'],
            ':password_hash' => $data['password_hash'],
            ':fname'       => $data['fname'],
            ':lname'       => $data['lname'],
            ':phone'       => $data['phone'],
            ':id'          => $id
        ]);
    }

    public function deleteRegistration(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM user_registrations WHERE reg_id = :id");
        $stmt->execute([':id' => $id]);
    }

    public function createRegistration(array $data): void
    {
        $sql = "
            INSERT INTO user_registrations
            (username, first_name, last_name, email, phone_number, password_hash,
             verification_token, verification_sent_at, expires_at)
            VALUES
            (:username, :fname, :lname, :email, :phone, :password_hash,
             :token, :sent_at, :expires_at)
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':username'      => $data['username'],
            ':fname'         => $data['fname'],
            ':lname'         => $data['lname'],
            ':email'         => $data['email'],
            ':phone'         => $data['phone'],
            ':password_hash' => $data['password_hash'],
            ':token'         => $data['token'],
            ':sent_at'       => $data['sent_at'],
            ':expires_at'    => $data['expires_at']
        ]);
    }

    public function makeUniqueUsername(string $base): string
    {
        $username = $base;
        $i = 1;
        $stmt = $this->pdo->prepare("
            SELECT username FROM (
                SELECT username FROM users
                UNION
                SELECT username FROM user_registrations
            ) AS combined
            WHERE username = :u
            LIMIT 1
        ");

        while (true) {
            $stmt->execute([':u' => $username]);
            if (!$stmt->fetch()) break;
            $username = $base . $i;
            $i++;
        }
        return $username;
    }
}
