<?php

namespace App\Services;

use App\Repositories\FavoriteRepository;

class FavoriteService
{
    private FavoriteRepository $favRepo;

    public function __construct()
    {
        $this->favRepo = new FavoriteRepository();
    }

    public function toggleFavorite(int $userId, int $productId): array
    {
        $existing = $this->favRepo->findFavorite($userId, $productId);
        
        if ($existing) {
            $this->favRepo->removeFavorite((int)$existing['favorite_id']);
            return ['success' => true, 'action' => 'removed'];
        } else {
            $this->favRepo->addFavorite($userId, $productId);
            return ['success' => true, 'action' => 'added'];
        }
    }
}
