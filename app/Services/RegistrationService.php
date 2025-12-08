<?php

namespace App\Services;

use App\Repositories\AuthRepository;
use App\Repositories\RegistrationRepository;

class RegistrationService
{
    private AuthRepository $authRepo;
    private RegistrationRepository $regRepo;

    public function __construct()
    {
        $this->authRepo = new AuthRepository();
        $this->regRepo = new RegistrationRepository();
    }

    public function registerUser(array $input): array
    {
        $fname    = trim($input['fname']);
        $lname    = trim($input['lname']);
        $email    = strtolower(trim($input['email']));
        $phone    = trim($input['phone_number']);
        $password = $input['password'];

        if ($this->authRepo->isEmailRegistered($email)) {
            return ['success' => false, 'errors' => ["Email already registered. Please log in or reset your password."]];
        }

        $pending = $this->regRepo->findPendingByEmail($email);
        
        $token      = bin2hex(random_bytes(16));
        $sent_at    = date('Y-m-d H:i:s');
        $expires_at = date('Y-m-d H:i:s', time() + 3600);
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Data array for update/insert
        $data = [
            'fname' => $fname,
            'lname' => $lname,
            'email' => $email,
            'phone' => $phone,
            'password_hash' => $password_hash,
            'token' => $token,
            'sent_at' => $sent_at,
            'expires_at' => $expires_at
        ];

        // 1. Throttle check
        if ($pending) {
            if (strtotime($pending['expires_at']) > time()) {
                // Not expired
                if (!empty($pending['verification_sent_at'])) {
                    $throttleSeconds = 60;
                    $lastSent = strtotime($pending['verification_sent_at']);
                    if ($lastSent !== false && (time() - $lastSent) < $throttleSeconds) {
                         return ['success' => false, 'errors' => ["A verification email was recently sent. Please wait a minute."]];
                    }
                }
                
                // Update
                $this->regRepo->updateRegistration($pending['reg_id'], $data);
                
                return [
                    'success' => true, 
                    'email' => $email, 
                    'token' => $token,
                    'redirect' => "/Leilife/public/index.php?page=verify_notice"
                ];
            } else {
                // Expired, delete
                $this->regRepo->deleteRegistration($pending['reg_id']);
            }
        }

        // 2. Create New
        $base_username = strtolower(preg_replace('/\s+/', '', $fname . '.' . $lname));
        $username = $this->regRepo->makeUniqueUsername($base_username);
        $data['username'] = $username;
        
        $this->regRepo->createRegistration($data);
        
        return [
            'success' => true, 
            'email' => $email, 
            'token' => $token,
            'redirect' => "/Leilife/public/index.php?page=verify_notice"
        ];
    }
}
