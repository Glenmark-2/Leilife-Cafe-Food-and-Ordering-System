<?php

namespace App\Controllers;

use App\Services\OrderService;
use App\Helpers\ResponseHelper;

class OrderController
{
    private OrderService $orderService;

    public function __construct()
    {
        $this->orderService = new OrderService();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function placeOrder(): void
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            ResponseHelper::jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            ResponseHelper::jsonResponse(['success' => false, 'message' => 'User not logged in.']);
        }

        // Handle JSON or Form input
        $rawInput = file_get_contents("php://input");
        $data = json_decode($rawInput, true);
        if (!$data && !empty($_POST)) {
            $data = $_POST;
        }

        $paymentMethod = $data['payment_method'] ?? null;
        $deliveryMethod = $data['delivery_method'] ?? null;

        if (!$paymentMethod) {
            ResponseHelper::jsonResponse(['success' => false, 'message' => 'No payment method selected.']);
        }

        try {
            $result = $this->orderService->placeOrder((int)$userId, $paymentMethod, (string)$deliveryMethod);
            ResponseHelper::jsonResponse($result);
        } catch (\Exception $e) {
            ResponseHelper::jsonResponse(['success' => false, 'message' => 'Order failed: ' . $e->getMessage()]);
        }
    }

    public function cancelOrder(): void
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            ResponseHelper::jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            ResponseHelper::jsonResponse(['success' => false, 'message' => 'User not logged in.']);
        }

        $orderNumber = $_POST['order_number'] ?? null;
        if (!$orderNumber) {
            ResponseHelper::jsonResponse(['success' => false, 'message' => 'Missing order number.']);
        }
        
        try {
            $result = $this->orderService->cancelOrder((int)$userId, $orderNumber);
            ResponseHelper::jsonResponse($result);
        } catch(\Exception $e) {
            // Need to return debug info if dev? Original returned debug trace.
            // Let's stick to message for security, or consistent 'success'=>false.
            ResponseHelper::jsonResponse([
                'success' => false, 
                'message' => 'Cancellation failed: ' . $e->getMessage()
            ]);
        }
    }
}
