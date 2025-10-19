<?php
// Make sure there’s NOTHING (spaces, BOM) before <?php
require_once __DIR__ . "/env.php";
loadEnv(__DIR__ . "/../../.env");

// Always send JSON headers
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *"); // optional but helpful

// Build response
$response = [
    "ORS_API_KEY" => $_ENV['ORS_API_KEY'] ?? ''
];

// Echo clean JSON only
echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
exit;
