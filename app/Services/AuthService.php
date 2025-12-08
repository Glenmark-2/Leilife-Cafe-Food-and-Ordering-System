<?php

namespace App\Services;

use App\Repositories\AuthRepository;
use App\Repositories\CartRepository;
use App\Config\Database;
use PDO;
use Exception;

class AuthService
{
    private AuthRepository $authRepo;
    private CartRepository $cartRepo;
    private PDO $pdo;

    public function __construct()
    {
        $this->authRepo = new AuthRepository();
        $this->cartRepo = new CartRepository();
        $this->pdo = Database::getConnection();
    }

    public function login(string $login, string $password, string $currentSessionId, ?string $guestToken): array
    {
        // 1. Admin Login
        $admin = $this->authRepo->findAdminByLogin($login);
        if ($admin) {
            if ((int)$admin['is_active'] === 0) {
                return ['success' => false, 'errors' => ["This account is temporarily deactivated. Please contact the system administrator."]];
            }
            if (password_verify($password, $admin['password'])) {
                // Trigger background process
                $cronPath = __DIR__ . "/../../backend/sentiment_cron.php";
                if (file_exists($cronPath)) {
                   // Using the path from original file logic, simplified
                   $cmd = "C:\\xampp\\php\\php.exe " . $cronPath . " > NUL 2>&1 &";
                   @exec($cmd);
                }
                
                return [
                    'success' => true,
                    'type' => 'admin',
                    'data' => $admin,
                    'redirect' => '/Leilife/public/admin.php?page=dashboard'
                ];
            }
        }

        // 2. Driver Login
        $driver = $this->authRepo->findDriverByLogin($login);
        if ($driver) {
            if ((int)$driver['is_active'] === 0) {
                return ['success' => false, 'errors' => ["This account is temporarily deactivated. Please contact the system administrator."]];
            }
            if (password_verify($password, $driver['password'])) {
                return [
                    'success' => true,
                    'type' => 'driver',
                    'data' => $driver,
                    'redirect' => '/Leilife/public/driver.php?page=dashboard'
                ];
            }
        }

        // 3. User Login
        $user = $this->authRepo->findUserByLogin($login);
        if ($user && password_verify($password, $user['password_hash'])) {
            // Cart Merge Logic
            try {
                $this->pdo->beginTransaction();
                
                $this->cartRepo->mergeCarts((int)$user['user_id'], $guestToken, $currentSessionId);
                
                $this->pdo->commit();
            } catch (Exception $e) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                // Log error if needed, but for now rethrow or return error
                 error_log("Cart merge error: " . $e->getMessage());
                 // We proceed with login even if cart merge fails? 
                 // Original code catches exception and logs it, then proceeds to login success!
            }

            return [
                'success' => true,
                'type' => 'user',
                'data' => $user,
                'redirect' => '/Leilife/public/index.php?page=home'
            ];
        }

        return ['success' => false, 'errors' => ["Invalid email/username or password."]];
    }
}
