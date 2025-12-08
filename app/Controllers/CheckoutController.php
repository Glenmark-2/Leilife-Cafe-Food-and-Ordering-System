<?php

namespace App\Controllers;

use App\Services\CheckoutService;
use App\Helpers\ResponseHelper;

class CheckoutController
{
    private CheckoutService $checkoutService;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->checkoutService = new CheckoutService();
    }

    public function handleRequest(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            ResponseHelper::jsonResponse(['success' => false, 'message' => 'Not logged in.'], 401);
        }

        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            $data = $this->checkoutService->getCheckoutData((int)$userId);
            
            // Replicate original session caching behavior
            foreach ($data as $k => $v) {
                $_SESSION[$k] = $v ?? '';
            }

            ResponseHelper::jsonResponse(['success' => true, 'data' => $data]);
        } 
        
        if ($method === 'POST') {
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true);

            if (!is_array($input)) {
                ResponseHelper::jsonResponse(['success' => false, 'message' => 'Invalid JSON body'], 400);
            }

            $action = $input['action'] ?? '';

            if ($action === 'update') {
                $this->checkoutService->updateAddress((int)$userId, $input);
                
                // Update Session
                $_SESSION['street_address'] = trim($input['street_address'] ?? '');
                $_SESSION['barangay']       = trim($input['barangay'] ?? '');
                $_SESSION['city']           = trim($input['city'] ?? '');
                $_SESSION['region']         = trim($input['region'] ?? '');
                $_SESSION['province']       = trim($input['province'] ?? '');
                $_SESSION['note_to_rider']  = trim($input['note_to_rider'] ?? '');

                ResponseHelper::jsonResponse(['success' => true, 'message' => 'Address updated.']);
            }

            if ($action === 'update_phone') {
                $phone = $input['phone_number'] ?? '';
                $this->checkoutService->updatePhoneNumber((int)$userId, $phone);
                
                $_SESSION['phone_number'] = trim($phone);
                
                ResponseHelper::jsonResponse(['success' => true, 'message' => 'Phone updated.']);
            }

            ResponseHelper::jsonResponse(['success' => false, 'message' => 'Unknown action.'], 400);
        }

        ResponseHelper::jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
}
