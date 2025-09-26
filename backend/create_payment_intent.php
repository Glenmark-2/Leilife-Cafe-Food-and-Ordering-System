<?php
// create_payment_intent.php
// Helper for creating PayMongo Payment Intents

function createPaymentIntent($amount, $order_id) {
    $secretKey = getenv("PAYMONGO_SECRET_KEY");

    $ch = curl_init("https://api.paymongo.com/v1/payment_intents");

    $data = [
        "data" => [
            "attributes" => [
                "amount" => intval($amount * 100), // PayMongo expects cents
                "payment_method_allowed" => ["gcash"],
                "currency" => "PHP",
                "description" => "Order #{$order_id}",
                "statement_descriptor" => "Order #{$order_id}",
                "capture_type" => "automatic",
                "metadata" => [
                    "order_id" => $order_id   // ✅ important for webhook
                ]
            ]
        ]
    ];

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Basic " . base64_encode($secretKey . ":")
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception("cURL error: " . $error);
    }

    curl_close($ch);
    $decoded = json_decode($result, true);

    if (isset($decoded['errors'])) {
        throw new Exception("PayMongo error: " . json_encode($decoded['errors']));
    }

    $piId = $decoded['data']['id'] ?? null;

    // Create PM + attach it, now we pass $order_id
    $pm = createPaymentMethodGCash($secretKey);
    $attach = attachPaymentMethodToIntent($secretKey, $piId, $pm, $order_id);

    return [
        "id" => $piId,
        "checkout_url" => $attach['next_action']['redirect']['url'] ?? null
    ];
}


// ✅ Create a Payment Method for GCash
function createPaymentMethodGCash($secretKey, $billing = []) {
    $ch = curl_init("https://api.paymongo.com/v1/payment_methods");

    $data = [
        "data" => [
            "attributes" => [
                "type" => "gcash",
                "billing" => [
                    "name" => $billing['name'] ?? "GCash User",
                    "email" => $billing['email'] ?? "customer@example.com",
                    "phone" => $billing['phone'] ?? "+639000000000"
                ]
            ]
        ]
    ];

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Basic " . base64_encode($secretKey . ":")
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);

    $result = curl_exec($ch);
    curl_close($ch);
    $decoded = json_decode($result, true);

    if (isset($decoded['errors'])) {
        throw new Exception("Create PM error: " . json_encode($decoded['errors']));
    }

    return $decoded['data']['id'];
}

// ✅ Attach Payment Method to Payment Intent
function attachPaymentMethodToIntent($secretKey, $piId, $pmId, $order_id) {
    $ch = curl_init("https://api.paymongo.com/v1/payment_intents/{$piId}/attach");

    $data = [
        "data" => [
            "attributes" => [
                "payment_method" => $pmId,
                "return_url" => "http://localhost/Leilife/public/index.php?page=thankyou&order_id={$order_id}"
            ]
        ]
    ];

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Basic " . base64_encode($secretKey . ":")
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);

    $result = curl_exec($ch);
    curl_close($ch);
    $decoded = json_decode($result, true);

    if (isset($decoded['errors'])) {
        throw new Exception("Attach error: " . json_encode($decoded['errors']));
    }

    return $decoded['data']['attributes'];
}

