<?php
// create_payment_intent.php
// Helpers for PayMongo Payment Intents and Refunds

function createPaymentIntent($amount, $order_id) {
    $secretKey = getenv("PAYMONGO_SECRET_KEY");

    $ch = curl_init("https://api.paymongo.com/v1/payment_intents");

    $data = [
        "data" => [
            "attributes" => [
                "amount" => intval($amount * 100), // PayMongo expects centavos
                "payment_method_allowed" => ["gcash"],
                "currency" => "PHP",
                "description" => "Order #{$order_id}",
                "statement_descriptor" => "Order #{$order_id}",
                "capture_type" => "automatic",
                "metadata" => [
                    "order_id" => $order_id
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

    // Create Payment Method + attach
    $pm = createPaymentMethodGCash($secretKey);
    $attach = attachPaymentMethodToIntent($secretKey, $piId, $pm, $order_id);

    return [
        "id" => $piId,
        "checkout_url" => $attach['next_action']['redirect']['url'] ?? null
    ];
}

// Create a Payment Method for GCash
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

// Attach Payment Method to Payment Intent
function attachPaymentMethodToIntent($secretKey, $piId, $pmId, $order_id) {
    // Detect current origin (works for localhost or production)
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = "{$protocol}://{$host}";

    // Use dynamic return URL
    $returnUrl = "{$baseUrl}/Leilife/public/index.php?page=thankyou&order_id={$order_id}";

    $ch = curl_init("https://api.paymongo.com/v1/payment_intents/{$piId}/attach");

    $data = [
        "data" => [
            "attributes" => [
                "payment_method" => $pmId,
                "return_url" => $returnUrl
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


// Refund API
// ----------- corrected createRefund -----------
function createRefund(string $payment_id, $amount_pesos, $order_id = null, $item_id = null, $reason = "requested_by_customer") {
    $secretKey = getenv("PAYMONGO_SECRET_KEY");
    if (!$secretKey) throw new Exception("Missing PAYMONGO_SECRET_KEY.");
    if (!$payment_id) throw new Exception("Missing payment_id for refund.");

    $amount_cents = intval(round(floatval($amount_pesos) * 100));
    if ($amount_cents <= 0) throw new Exception("Refund amount must be greater than zero.");

    $valid_reasons = ["duplicate", "fraudulent", "requested_by_customer"];
    if (!in_array($reason, $valid_reasons, true)) $reason = "requested_by_customer";

    // ✅ Always check remaining refundable, but never block if delay causes mismatch
    $remaining = getRemainingRefundable($payment_id);
    if (!is_int($remaining)) $remaining = $amount_cents; // fallback
    if ($remaining <= 0) {
        error_log("⚠️ Warning: PayMongo shows remaining refundable = 0 for $payment_id, but proceeding anyway (may be delay).");
    } elseif ($amount_cents > $remaining) {
        $formatted_requested = number_format($amount_cents / 100, 2);
        $formatted_remaining = number_format($remaining / 100, 2);
        throw new Exception("Refund exceeds remaining refundable. Requested: PHP {$formatted_requested}, Remaining: PHP {$formatted_remaining}");
    }

    // ✅ Unique metadata each refund
    $payload = [
        "data" => [
            "attributes" => [
                "amount"     => $amount_cents,
                "reason"     => $reason,
                "payment_id" => $payment_id,
                "metadata"   => [
                    "order_id" => $order_id,
                    "item_id"  => $item_id,
                    "refund_ref" => uniqid("ref_") // unique tag for each refund
                ]
            ]
        ]
    ];

    $ch = curl_init("https://api.paymongo.com/v1/refunds");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Basic " . base64_encode($secretKey . ":")
        ]
    ]);

    $resp = curl_exec($ch);
    $curlErr = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlErr) throw new Exception("cURL error during refund: " . $curlErr);
    $decoded = json_decode($resp, true);
    if (json_last_error() !== JSON_ERROR_NONE) throw new Exception("Invalid JSON response: " . $resp);

    // ✅ if refund request succeeds, PayMongo always returns "data.id"
    if ($httpCode >= 200 && $httpCode < 300 && isset($decoded['data']['id'])) {
        $data = $decoded['data'];
        return [
            'id'     => $data['id'],
            'status' => $data['attributes']['status'] ?? null,
            'amount' => $data['attributes']['amount'] ?? 0,
        ];
    }

    // fallback for PayMongo errors
    if (isset($decoded['errors'])) throw new Exception("PayMongo refund error: " . json_encode($decoded['errors']));
    throw new Exception("Unexpected refund response: " . $resp);
}


function getRemainingRefundable(string $payment_id): int {
    $secretKey = getenv("PAYMONGO_SECRET_KEY");
    if (!$secretKey) {
        throw new Exception("Missing PAYMONGO_SECRET_KEY environment variable.");
    }

    $url = "https://api.paymongo.com/v1/payments/" . urlencode($payment_id);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Basic " . base64_encode($secretKey . ":"),
            "Content-Type: application/json"
        ],
    ]);
    $resp = curl_exec($ch);
    $curlErr = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlErr) {
        throw new Exception("cURL error fetching refundable: " . $curlErr);
    }
    if ($httpCode < 200 || $httpCode >= 300) {
        throw new Exception("HTTP $httpCode fetching refundable: " . $resp);
    }

    $decoded = json_decode($resp, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON from PayMongo: " . $resp);
    }

    if (isset($decoded['errors'])) {
        throw new Exception("PayMongo error: " . json_encode($decoded['errors']));
    }

    $attr = $decoded['data']['attributes'] ?? null;
    if (!$attr) {
        throw new Exception("Invalid payment structure from PayMongo.");
    }

    // Log full attributes for debugging
    error_log("PAYMONGO ATTRIBUTES: " . json_encode($attr));

    // Case 1: PayMongo gives amount_refundable directly
    if (isset($attr['amount_refundable'])) {
        return (int)$attr['amount_refundable'];
    }

    // Case 2: manually compute remaining refund
    $amount_total = (int)($attr['amount'] ?? 0);
    $refunded_amount = 0;

    // ✅ Fix: correctly read nested refunds
    if (isset($attr['refunds']) && is_array($attr['refunds'])) {
        foreach ($attr['refunds'] as $r) {
            if (!isset($r['attributes'])) continue;
            $refund = $r['attributes'];
            $status = $refund['status'] ?? '';
            $amt = (int)($refund['amount'] ?? 0);
            if ($status === 'succeeded') {
                $refunded_amount += $amt;
            }
        }
    }

    $remaining = $amount_total - $refunded_amount;
    if ($remaining < 0) $remaining = 0;

    // Log the computed refund values
    error_log("Computed remaining refund: total={$amount_total}, refunded={$refunded_amount}, remaining={$remaining}");

    return $remaining;
}
