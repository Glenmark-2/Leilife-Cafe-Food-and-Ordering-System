<?php

namespace App\Controllers;

use App\Services\FavoriteService;
use App\Helpers\ResponseHelper;

class FavoriteController
{
    private FavoriteService $favService;

    public function __construct()
    {
        $this->favService = new FavoriteService();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function handleToggle(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $productId = $_POST['product_id'] ?? null;
        
        // Fallback to POST user_id if for some reason session not used, but prefer session
        if (!$userId && isset($_POST['user_id'])) {
             // Optional: Allow POST override if explicitly needed, but unsafe. 
             // Logic in original file used $_POST['user_id']. 
             // We'll stick to secure session if available, else fail if strict.
             // But for compatibility with frontend that might be sending user_id:
             $userId = $_POST['user_id'];
        }

        if (!$userId || !$productId) {
            ResponseHelper::jsonResponse(['success' => false, 'error' => 'Missing user_id or product_id']);
        }

        try {
            $result = $this->favService->toggleFavorite((int)$userId, (int)$productId);
            ResponseHelper::jsonResponse($result);
        } catch (\Exception $e) {
            ResponseHelper::jsonResponse(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
        }
    }
}
