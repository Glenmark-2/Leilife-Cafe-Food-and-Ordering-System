<?php

namespace App\Controllers;

use App\Services\AuthService;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function handleLogin(): void
    {
        header("Content-Type: application/json");

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            http_response_code(405);
            echo json_encode(["success" => false, "errors" => ["Invalid request method."]]);
            return;
        }

        // CSRF validation
        if (
            !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
            $_POST['csrf_token'] !== $_SESSION['csrf_token']
        ) {
            http_response_code(403);
            echo json_encode(["success" => false, "errors" => ["Security validation failed. Please refresh and try again."]]);
            return;
        }

        $login    = strtolower(trim($_POST["login"] ?? ''));
        $password = trim($_POST["password"] ?? '');

        if (empty($login) || empty($password)) {
            echo json_encode(["success" => false, "errors" => ["Please fill in all fields."]]);
            return;
        }

        $guestToken = $_COOKIE['guest_token'] ?? null;
        $sessionId = session_id();

        $result = $this->authService->login($login, $password, $sessionId, $guestToken);

        if ($result['success']) {
            $data = $result['data'];
            $type = $result['type'];

            if ($type === 'admin') {
                $_SESSION['admin_id']    = $data['admin_id'];
                $_SESSION['admin_name']  = $data['full_name'];
                $_SESSION['admin_email'] = $data['email'];
                $_SESSION['show_welcome'] = true;
            } elseif ($type === 'driver') {
                $_SESSION['driver_id']    = $data['driver_id'];
                $_SESSION['driver_name']  = $data['full_name'];
                $_SESSION['driver_email'] = $data['email'];
                $_SESSION['show_welcome'] = true;
            } elseif ($type === 'user') {
                if (isset($_COOKIE['guest_token'])) {
                    setcookie("guest_token", "", time() - 3600, "/");
                    unset($_COOKIE['guest_token']);
                }

                $_SESSION['user_id']  = $data['user_id'];
                $_SESSION['username'] = $data['username'];
                $_SESSION['email']    = $data['email'];
                session_regenerate_id(true);
            }

            echo json_encode([
                "success"  => true,
                "redirect" => $result['redirect']
            ]);
        } else {
             // Handle errors
             $errors = $result['errors'] ?? ["Invalid credentials"];
             echo json_encode(["success" => false, "errors" => $errors]);
        }
    }
    public function handleLogout(): void
    {
        // Unset all session variables
        $_SESSION = [];

        // Destroy the session
        session_destroy();

        // Redirect
        header("Location: /Leilife/public/index.php?page=home");
        exit;
    }
}
