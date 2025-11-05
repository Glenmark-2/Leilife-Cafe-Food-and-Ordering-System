<?php
require_once __DIR__ . "/db_script/env.php";
header("Content-Type: application/json");

try {
    loadEnv(__DIR__ . "/../.env");
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
    exit;
}

$ORS_API_KEY = $_ENV["ORS_API_KEY"] ?? null;

if (!$ORS_API_KEY) {
    echo json_encode(["success" => false, "message" => "Missing ORS_API_KEY"]);
    exit;
}

$from = $_GET["from"] ?? null;
$to   = $_GET["to"] ?? null;

if (!$from || !$to) {
    echo json_encode(["success" => false, "message" => "Missing coordinates"]);
    exit;
}

$fromArr = explode(",", $from);
$toArr   = explode(",", $to);

$body = [
    "coordinates" => [
        [floatval($fromArr[0]), floatval($fromArr[1])],
        [floatval($toArr[0]),   floatval($toArr[1])]
    ]
];

$curl = curl_init("https://api.openrouteservice.org/v2/directions/driving-car");
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: $ORS_API_KEY"
]);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$res = curl_exec($curl);
curl_close($curl);

$data = json_decode($res, true);

// ✅ FIX — ORS uses routes[], NOT features[]
if (!isset($data["routes"][0]["summary"])) {
    echo json_encode([
        "success" => false,
        "message" => "No route found",
        "raw" => $data
    ]);
    exit;
}

$summary = $data["routes"][0]["summary"];

echo json_encode([
    "success"  => true,
    "duration" => $summary["duration"],   // seconds
    "distance" => $summary["distance"]    // meters
]);
