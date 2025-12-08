<?php

namespace App\Controllers;

use App\Services\CartService;
use App\Helpers\ResponseHelper;

class CartController
{
    private CartService $cartService;

    public function __construct()
    {
        $this->cartService = new CartService();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function getSessionData(): array
    {
        return [
            'sessionId' => session_id(),
            'userId' => $_SESSION['user_id'] ?? null,
            'guestToken' => $_COOKIE['guest_token'] ?? null
        ];
    }

    public function getCart(): void
    {
        $s = $this->getSessionData();
        $result = $this->cartService->getCart($s['sessionId'], $s['userId'], $s['guestToken']);
        
        ResponseHelper::jsonResponse($result);
    }

    public function addToCart(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             ResponseHelper::jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $s = $this->getSessionData();
        
        // Handle guest token creation if needed
        if (!$s['userId'] && !$s['guestToken']) {
            $guestToken = bin2hex(random_bytes(16));
            setcookie("guest_token", $guestToken, time() + (86400*30), "/");
            $s['guestToken'] = $guestToken;
        }

        // Normalize input
        // Original add_to_cart uses $_POST directly.
        // We can just pass $_POST.
        $input = [
            'product_id' => $_POST['product_id'] ?? null,
            'quantity' => $_POST['quantity'] ?? 1,
            'size' => $_POST['size'] ?? null,
            'flavor_ids' => json_decode($_POST['flavors'] ?? '[]', true)
        ];
        
        if (!$input['product_id']) {
            ResponseHelper::jsonResponse(['success' => false, 'message' => 'No product ID']);
        }

        $result = $this->cartService->addToCart($input, $s['sessionId'], $s['userId'], $s['guestToken']);
        ResponseHelper::jsonResponse($result);
    }
    
    public function updateCart(): void
    {
        $raw = file_get_contents("php://input");
        $data = json_decode($raw, true);

        if (!is_array($data) || empty($data['action'])) {
            ResponseHelper::jsonResponse(['success' => false, 'message' => 'Invalid request']);
        }

        $s = $this->getSessionData();

        try {
            $result = $this->cartService->updateCart($data, $s['sessionId'], $s['userId'], $s['guestToken']);
            ResponseHelper::jsonResponse($result);
        } catch (\Exception $e) {
            ResponseHelper::jsonResponse(['success' => false, 'message' => 'Cart update failed', 'error' => $e->getMessage()]);
        }
    }

    public function checkCart(): void
    {
        // For check_cart.php which returns { hasItems: bool }
        $s = $this->getSessionData();
        // check_cart.php only checked user_id || session_id, but logically should use same lookup
        // But original check_cart.php starts with `if (!$user_id)`. Meaning it only works for logged in users?
        // Let's check original logic:
        /*
            $user_id = $_SESSION['user_id'] ?? null;
            if (!$user_id) { echo .. exit; }
        */
        // Yes, it enforces login.
        
        if (!$s['userId']) {
             ResponseHelper::jsonResponse(['hasItems' => false]);
        }

        // It checks if "cart exists" AND "items > 0".
        $count = $this->cartService->getCartCount($s['sessionId'], $s['userId'], $s['guestToken']);
        
        ResponseHelper::jsonResponse(['hasItems' => $count > 0]);
    }

    public function getCartCount(): void
    {
        // For update_cart_counter.php
        // returns { success: true, count: int }
        $s = $this->getSessionData();
        $count = $this->cartService->getCartCount($s['sessionId'], $s['userId'], $s['guestToken']);
        
        ResponseHelper::jsonResponse(['success' => true, 'count' => $count]);
    }
}
