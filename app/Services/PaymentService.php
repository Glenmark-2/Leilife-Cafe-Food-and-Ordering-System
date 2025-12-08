<?php

namespace App\Services;

use App\Config\EnvLoader;
use Exception;
use PDO;
use App\Config\Database;

class PaymentService
{
    private string $secretKey;
    private PDO $pdo; // For attachPaymentMethod requirement of reading orders (legacy logic)

    public function __construct()
    {
        $this->secretKey = getenv("PAYMONGO_SECRET_KEY") ?: '';
        $this->pdo = Database::getConnection(); // We might need this for the legacy logic inside attach
    }

    public function createPaymentIntent(float $amount, int $orderId): array
    {
        // 1. Create PI
        $piData = [
            "data" => [
                "attributes" => [
                    "amount" => intval($amount * 100),
                    "payment_method_allowed" => ["gcash"],
                    "currency" => "PHP",
                    "description" => "Order #{$orderId}",
                    "statement_descriptor" => "Order #{$orderId}",
                    "capture_type" => "automatic",
                    "metadata" => [
                        "order_id" => (string)$orderId // Ensure string if PayMongo strictly checks types, but likely just avoiding nesting issues
                    ]
                ]
            ]
        ];

        /*
           PayMongo metadata should be flat key-value pairs.
           Previous error: "metadata attributes cannot be nested."
           My implementation: "metadata" => ["order_id" => $orderId]
           This looks flat to me?
           Wait, if $orderId is somehow an array or object? It's typed int.
           Maybe "metadata" inside "attributes" is correct.
           Wait, maybe PayMongo expects metadata values to be strings?
           Let's cast to string.
           Also checking legacy code:
           "metadata" => [ "order_id" => $order_id ]
           Legacy code worked.
           
           Is it possible I am sending it wrapped weirdly?
           Ah, I am using json_encode in callPayMongo().
           The array structure:
           [ "data" => [ "attributes" => [ ... "metadata" => [ key => val ] ] ] ]
           
           The error says: "source": {"pointer": "metadata", "attribute": "metadata"}
           "detail": "metadata attributes cannot be nested."
           
           This error usually means one of the VALUES in metadata is an array.
           $orderId is int.
           
           Let's look closely at `callPayMongo`.
           It json_encodes the data.
           
           Is it possible that `createPaymentIntent` logic in `PaymentService` is somehow incorrect compared to legacy?
           Legacy:
           $data = [ "data" => [ "attributes" => [ ... "metadata" => [ "order_id" => $order_id ] ] ] ];
           
           My Code:
           $piData = [ "data" => [ "attributes" => [ ... "metadata" => ["order_id" => $orderId] ] ] ];
           
           Identical structure.
           
           Maybe the issue is somewhere else?
           "metadata attributes cannot be nested."
           This definitely means you passed a nested object/array as a value for a metadata key.
           Example that fails: "metadata" => [ "info" => [ "foo" => "bar" ] ]
           
           Here: "metadata" => [ "order_id" => 123 ]
           Unless $orderId is not an int?
           It is typed `int $orderId`.
           
           Wait. Is it possible that `json_encode` is being called TWICE?
           `callPayMongo` calls `json_encode($data)`.
           `createPaymentIntent` passes array.
           This is correct.
           
           Let's cast $orderId to string explicitly just in case PayMongo is being weird about numbers (though unlikely to cause "nested" error).
           
           Wait, I see `createRefund` also uses metadata.
           "refund_ref" => uniqid("ref_")
           "order_id" => $orderId
           
           The error happened when "placing order". So it's `createPaymentIntent`.
           
           Let's make absolutely sure `orderId` is scalar.
           
           What if I am reusing `$piData` incorrectly? No, it's local.
           
           Let's try to simplify metadata to simpler array.
        */
        
        $piData = [
            "data" => [
                "attributes" => [
                    "amount" => intval($amount * 100),
                    "payment_method_allowed" => ["gcash"],
                    "currency" => "PHP",
                    "description" => "Order #{$orderId}",
                    "statement_descriptor" => "Order #{$orderId}",
                    "capture_type" => "automatic",
                    "metadata" => [
                        "order_id" => (string)$orderId
                    ]
                ]
            ]
        ];

        $piResult = $this->callPayMongo('payment_intents', $piData);
        $piId = $piResult['data']['id'];

        // 2. Create PM
        $pmResult = $this->createPaymentMethodGCash();
        $pmId = $pmResult['data']['id'];

        // 3. Attach
        $attachResult = $this->attachPaymentMethodToIntent($piId, $pmId, $orderId);

        return [
            "id" => $piId,
            "checkout_url" => $attachResult['next_action']['redirect']['url'] ?? null
        ];
    }

    private function createPaymentMethodGCash(): array
    {
        $data = [
            "data" => [
                "attributes" => [
                    "type" => "gcash",
                    "billing" => [
                        "name" => "GCash User",
                        "email" => "customer@example.com",
                        "phone" => "+639000000000"
                    ]
                ]
            ]
        ];
        return $this->callPayMongo('payment_methods', $data);
    }

    private function attachPaymentMethodToIntent(string $piId, string $pmId, int $orderId): array
    {
        // Calculate return URL
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'] ?? 'bscs3b.com';
        $baseUrl = "{$protocol}://{$host}";

        // Need order number
        $stmt = $this->pdo->prepare("SELECT order_number FROM orders WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $orderNumber = $stmt->fetchColumn(); 
        
        // This is safe because user MUST exist for this flow to trigger in PlaceOrderService.

        $returnUrl = "{$baseUrl}/Leilife/public/index.php?page=order-tracking&num=" . rawurlencode($orderNumber);

        $data = [
            "data" => [
                "attributes" => [
                    "payment_method" => $pmId,
                    "return_url" => $returnUrl
                ]
            ]
        ];

        $result = $this->callPayMongo("payment_intents/{$piId}/attach", $data);
        return $result['data']['attributes'];
    }

    // Updated to support method
    private function callPayMongo(string $endpoint, array $data = [], string $method = 'POST'): array
    {
        $ch = curl_init("https://api.paymongo.com/v1/{$endpoint}");
        
        $headers = [
            "Content-Type: application/json",
            "Authorization: Basic " . base64_encode($this->secretKey . ":")
        ];

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        } elseif ($method === 'GET') {
            $options[CURLOPT_HTTPGET] = true;
        }

        curl_setopt_array($ch, $options);

        $result = curl_exec($ch);
        
        if (curl_errno($ch)) {
             throw new Exception("cURL error: " . curl_error($ch));
        }
        curl_close($ch);

        $decoded = json_decode($result, true);
        if (isset($decoded['errors'])) {
            throw new Exception("PayMongo error: " . json_encode($decoded['errors']));
        }

        return $decoded;
    }

    public function getRemainingRefundable(string $paymentId): int
    {
        try {
             $result = $this->callPayMongo("payments/" . urlencode($paymentId), [], 'GET');
             $attr = $result['data']['attributes'] ?? null;
             
             if (!$attr) throw new Exception("Invalid attributes from PayMongo");

             // Case 1: Direct field
             if (isset($attr['amount_refundable'])) {
                 return (int)$attr['amount_refundable'];
             }
             
             // Case 2: Manual calc
             $total = (int)($attr['amount'] ?? 0);
             $refunded = 0;
             if (isset($attr['refunds']) && is_array($attr['refunds'])) {
                 foreach ($attr['refunds'] as $r) {
                     if (($r['attributes']['status'] ?? '') === 'succeeded') {
                         $refunded += (int)($r['attributes']['amount'] ?? 0);
                     }
                 }
             }
             
             return max(0, $total - $refunded);

        } catch (Exception $e) {
            // Log warning in production? 
            throw $e;
        }
    }

    public function createRefund(string $paymentId, float $amountPesos, int $orderId, string $reason = 'requested_by_customer'): array
    {
        $cents = (int)round($amountPesos * 100);
        
        $data = [
            "data" => [
                "attributes" => [
                    "amount" => $cents,
                    "reason" => $reason,
                    "payment_id" => $paymentId,
                    "metadata" => [
                        "order_id" => $orderId,
                        "refund_ref" => uniqid("ref_")
                    ]
                ]
            ]
        ];

        $result = $this->callPayMongo("refunds", $data);
        return [
            'id' => $result['data']['id'],
            'status' => $result['data']['attributes']['status'] ?? 'pending',
            'amount' => ($result['data']['attributes']['amount'] ?? 0) / 100.0
        ];
    }
}
