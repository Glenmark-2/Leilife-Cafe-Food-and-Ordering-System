<?php

namespace App\Services;

use App\Repositories\OrderRepository;
use App\Repositories\CartRepository;
use App\Services\PaymentService;
use Exception;

class OrderService
{
    private OrderRepository $orderRepo;
    private CartRepository $cartRepo;
    private PaymentService $paymentService;

    public function __construct()
    {
        $this->orderRepo = new OrderRepository();
        $this->cartRepo = new CartRepository();
        $this->paymentService = new PaymentService();
    }

    public function placeOrder(int $userId, string $paymentMethod, string $deliveryMethod): array
    {
        // 1. Get Cart
        $cart = $this->cartRepo->findCartByUserId($userId);
        if (!$cart) {
            throw new Exception("Cart not found.");
        }
        
        // 2. Get Cart Items
        $cartItems = $this->cartRepo->getCartItems((int)$cart['cart_id']);
        if (empty($cartItems)) {
            throw new Exception("No items in cart.");
        }

        // 3. Begin Transaction
        $this->orderRepo->beginTransaction();

        try {
            // 4. Create Order
            $orderNumber = $this->generateOrderNumber();
            $orderId = $this->orderRepo->createOrder([
                'user_id' => $userId,
                'total' => $cart['total'],
                'payment_method' => $paymentMethod,
                'order_number' => $orderNumber,
                'delivery_method' => $deliveryMethod
            ]);

            // 5. Create Order Items
            foreach ($cartItems as $item) {
                // Logic for price: if size=large, use price_large
                $unitPrice = ($item['size'] === 'large') 
                    ? (float)$item['price_large'] 
                    : (float)$item['product_price'];

                $this->orderRepo->createOrderItem($orderId, [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $unitPrice,
                    'size' => $item['size'],
                    'flavor_ids' => $item['flavor_ids'] ?: null
                ]);
            }

            // 6. Handle Payment logic
            $responseData = [
                'success' => true,
                'order_id' => $orderId,
                'order_number' => $orderNumber
            ];

            if ($paymentMethod === 'gcash') {
                $pi = $this->paymentService->createPaymentIntent((float)$cart['total'], $orderId);
                $responseData['message'] = "Order created, redirecting to PayMongo.";
                $responseData['checkout_url'] = $pi['checkout_url'];
            } else {
                // COD
                // Clear cart (only for COD? Original code cleared cart for COD. Does Gcash clear cart on webhook success?)
                // Original: "if gcash ... else { clearCartStmt }"
                // Assuming PayMongo webhook handles cart clearing or it happens later.
                // Actually, if we redirect to PayMongo, we shouldn't clear cart yet until paid? 
                // Or maybe we should? Original code DID NOT clear cart for Gcash here.
                $this->cartRepo->removeCartItemsAll((int)$cart['cart_id']); // Need to add this method or loop remove?
                // Let's implement batch remove in Repo.
                
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
                $redirectUrl = "$protocol://$host/Leilife/public/index.php?page=order-tracking&num=" . urlencode($orderNumber);
                
                $responseData['message'] = "Order created successfully.";
                $responseData['redirect_url'] = $redirectUrl;
            }

            $this->orderRepo->commit();
            
            return $responseData;

        } catch (Exception $e) {
            $this->orderRepo->rollBack();
            throw $e;
        }
    }

    private function generateOrderNumber(): string
    {
        return "ORD-" . date("Ymd") . "-" . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    }

    public function cancelOrder(int $userId, string $orderNumber): array
    {
        // 1. Fetch Order and verify ownership
        $order = $this->orderRepo->findOrderByNumber($orderNumber);
        
        if (!$order) {
            throw new Exception("Order not found.");
        }
        if ((int)$order['user_id'] !== $userId) {
             throw new Exception("Order not owned by user.");
        }

        $orderId = (int)$order['order_id'];
        $status = $order['status'];
        
        $cancellable = ['pending', 'preparing'];
        // Let's be consistent with original code which checked these states.
        // Original: "pending", "preparing".
        
        if (!in_array($status, $cancellable)) {
            throw new Exception("Order cannot be cancelled at this stage.");
        }

        $this->orderRepo->beginTransaction();

        try {
            // 2. Cancellation updates
            $this->orderRepo->updateOrderStatus($orderId, 'cancelled');
            $this->orderRepo->updateAllOrderItemsStatus($orderId, 'cancelled');

            // 3. Recalc total (excluding cancelled, which is now all of them? 
            // Original code: "status != 'cancelled'". If we just set all to 'cancelled', total is 0?
            // "UPDATE order_items SET status = 'cancelled' WHERE order_id = ?"
            // "SELECT COALESCE(SUM(price * quantity), 0) ... WHERE ... status != 'cancelled'"
            // Yes, new total becomes 0.
            $newTotal = $this->orderRepo->getOrderSubtotalExcludingCancelled($orderId);
            $this->orderRepo->updateOrderTotal($orderId, $newTotal);

            // 4. Refund logic
            $refundResult = null;
            $paymentMethod = $order['payment_method'];
            $paymentStatus = $order['payment_status']; // 'paid' check in original
            $origTotal = (float)$order['total'];
            
            // Note: Original code checks if 'paid'.
            if ($paymentMethod === 'gcash' && $paymentStatus === 'paid') {
                 // Try to get payment ID from transaction log if order['payment_id'] is missing
                 // This assumes 'transactions' table exists and is populated by webhook.
                 // We need a Repo method for this.
                 
                 $paymentId = $order['payment_id'] ?? null;
                 if (!$paymentId) {
                     $paymentId = $this->orderRepo->getSuccessfulPaymentTransaction($orderId);
                 }

                 if ($paymentId) {
                      $remaining = $this->paymentService->getRemainingRefundable($paymentId);
                      
                      if ($remaining > 0) {
                          $refundResult = $this->paymentService->createRefund($paymentId, $origTotal, $orderId);
                          
                          // Mark as refund requested locally
                          $this->orderRepo->markRefundRequested($orderId);
                      } else {
                          $refundResult = ['success' => false, 'message' => 'No refundable balance.'];
                      }
                 } else {
                      $refundResult = ['success' => false, 'message' => 'Payment ID not found.'];
                 }
            }
            
            $this->orderRepo->commit();

            return [
                'success' => true,
                'message' => 'Order cancelled',
                'order_number' => $orderNumber,
                'order_id' => $orderId,
                'new_total' => $newTotal,
                'refund' => $refundResult
            ];

        } catch (Exception $e) {
            $this->orderRepo->rollBack();
            throw $e;
        }
    }
}
