<?php

namespace App\Controllers;

use App\Services\GoogleAuthService;
use App\Repositories\AuthRepository;
use Exception;

class SocialAuthController
{
    private GoogleAuthService $googleService;
    private AuthRepository $authRepo;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->googleService = new GoogleAuthService();
        $this->authRepo = new AuthRepository();
    }

    public function handleGoogleLogin(): void
    {
        error_reporting(E_ALL & ~E_DEPRECATED);

        // 1. Redirect to Google If No Code
        if (!isset($_GET['code'])) {
            $authUrl = $this->googleService->getAuthUrl();
            header("Location: $authUrl");
            exit;
        }

        try {
            // 2. Callback handling
            $userInfo = $this->googleService->handleCallback($_GET['code']);
            $email = $userInfo['email'];
            $picture = $userInfo['picture'];
            
            // 3. User Lookup
            $user = $this->authRepo->findUserByEmail($email);

            if ($user) {
                // Update profile pic if needed
                if (empty($user['profile_picture']) || $user['profile_picture'] !== $picture) {
                    $this->authRepo->updateProfilePicture((int)$user['user_id'], $picture);
                }
                
                if ($user['auth_provider'] === 'google') {
                    // Login
                    $_SESSION['profile_picture'] = $picture;
                    $_SESSION['user_id']  = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email']    = $user['email'];
                } elseif ($user['auth_provider'] === 'local') {
                    // Block
                    die("This email is already registered with a password. Please log in with your email and password.");
                }
            } else {
                // Register
                $nameParts = explode(" ", $userInfo['name'], 2);
                $firstName = $nameParts[0] ?? '';
                $lastName = $nameParts[1] ?? '';
                $username = strtolower(preg_replace('/\s+/', '', $firstName)) . rand(100, 999);
                
                $data = [
                    'username' => $username,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'google_id' => $userInfo['google_id'],
                    'profile_picture' => $picture
                ];
                
                $newUserId = $this->authRepo->createGoogleUser($data);
                
                $_SESSION['user_id'] = $newUserId;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
            }

            // Redirect home
            // Adjust path if needed.
            $baseUrl = '/Leilife/public/index.php?page=home';
            header("Location: $baseUrl");
            exit;

        } catch (Exception $e) {
            die("Google Login Error: " . htmlspecialchars($e->getMessage()));
        }
    }
}
