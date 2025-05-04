<?php

/*
MIT License
Copyright (c) 2025 Albdroid.AL
Created Date Sunday, 4 May 2025
*/

error_reporting(0);
// Replace with your OAuth token from Yandex
$oauth_token = "YOUR_OAUTH_TOKEN_HERE";

// The public URL of the Yandex Disk folder or file
//$url = "https://disk.yandex.com/d/Pb313mz4QK-JMg";
$url = isset($_GET["url"]) && !empty($_GET["url"]) ? $_GET["url"] : "https://disk.yandex.com/d/Pb313mz4QK-JMg";

// Get metadata from Yandex Disk public resource
function getPublicResourceMetadata($url, $oauth_token) {
    $apiUrl = "https://cloud-api.yandex.net/v1/disk/public/resources?public_key=" . urlencode($url);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: OAuth " . $oauth_token,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// Get titles and download URLs
function getTitlesAndUrls($url, $oauth_token) {
    $metadata = getPublicResourceMetadata($url, $oauth_token);

    if (isset($metadata["error"])) {
        die("Error: " . $metadata["error"] . " - " . $metadata["message"]);
    }

    $items = $metadata["_embedded"]["items"] ?? [];
    $results = [];

    foreach ($items as $item) {
        $results[] = [
            "title" => $item["name"],
            "stream_url" => $item["file"] ?? "Folder (no direct download URL)",
        ];
    }

    return $results;
}

// Fetch titles and URLs
$data = getTitlesAndUrls($url, $oauth_token);

// Print results
foreach ($data as $entry) {
header("Access-Control-Allow-Origin: *");
header("Content-type: application/json");
echo "Title: " . $entry["title"] . "\n";
echo "Stream URL: " . $entry["stream_url"] . "\n\n";
}