<?php
// api_proxy.php

header("Content-Type: application/json");

// Récupérer le JSON envoyé par le frontend
$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    echo json_encode(["success" => false, "error" => "No input data"]);
    exit;
}

$apiKey = $input["key"] ?? "";
$searchType = $input["type"] ?? "";
$field = $input["field"] ?? [];
$apis = $input["apis"] ?? [];

// Tableau qui stockera les résultats de chaque API
$results = [];

foreach ($apis as $api) {
    switch ($api) {
        case "leakcheck":
            $query = $field[$searchType] ?? "";
            $url = "https://leakcheck.net/api/?key={$apiKey}&check={$query}";
            $response = @file_get_contents($url);
            $results[$api] = $response ? json_decode($response, true) : ["success" => false, "error" => "No response"];
            break;

        case "snusbase":
            $query = $field[$searchType] ?? "";
            $url = "https://api.snusbase.com/data/search";
            $opts = [
                "http" => [
                    "method" => "POST",
                    "header" => "Content-Type: application/json\r\nAuthorization: {$apiKey}\r\n",
                    "content" => json_encode(["term" => $query])
                ]
            ];
            $context = stream_context_create($opts);
            $response = @file_get_contents($url, false, $context);
            $results[$api] = $response ? json_decode($response, true) : ["success" => false, "error" => "No response"];
            break;

        default:
            $results[$api] = ["success" => false, "error" => "API not implemented"];
            break;
    }
}

// Réponse finale
echo json_encode([
    "success" => true,
    "results" => $results
]);
