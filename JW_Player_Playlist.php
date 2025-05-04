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
//short_url https://yadi.sk/d/Pb313mz4QK-JMg
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
            "stream_url" => $item["file"] ?? "Folder (No Direct Download URL)",
        ];
    }

    return $results;
}

// Fetch titles and URLs
$data = getTitlesAndUrls($url, $oauth_token);

// Generate JW Player playlist JSON
$playlist = [];
$i = 1;
foreach ($data as $item) {
$title = $item["title"];
$title = substr($title, 0, (strlen ($title)) - (strlen (strrchr($title,"."))));
$stream = $item["stream_url"];

    $item = [
	"id" =>   $i++,
        "title" => trim($title),
        "file" => trim($stream),
		"image" => "https://png.kodi.al/tv/albdroid/logo_bar.png",
		"type" => "video/mp4",
		"label" => "HD",
    ];

    // Add thumbnail if available
    if (!empty($item["image"])) {
        $item["image"] = $item["image"];
    }

    $playlist[] = $item;
}

// Output the playlist as JSON
header("Access-Control-Allow-Origin: *");
$json_data = str_replace('\\/', '/', json_encode(["playlist" => $playlist],JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
//echo $json_data;
?>
<!doctype html>
<html>
<head>
<meta http-equiv="refresh" content="6200">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Albdroid Player</title>
<link rel="shortcut icon" href="https://kodi.al/panel.ico"/>
<link rel="icon" href="https://kodi.al/panel.ico"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
<meta name="description" content="JW Player Code Builder" />
<meta name="author" content="Olsion Bakiaj - Endrit Pano" />
<meta property="og:site_name" content="JW Player Code Builder">
<meta property="og:locale" content="en_US">
<meta name="msapplication-TileColor" content="#0F0">
<meta name="theme-color" content="#0F0">
<meta name="msapplication-navbutton-color" content="#0F0">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="#0F0">
<body oncontextmenu="return false;">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css">
<script src="https://content.jwplatform.com/libraries/CoaM1Zse.js"></script>
<script>
    jwplayer.key = 'AxtUTRaRN2XoKSqfng16IByVxBY6mENRZp0DVw==';
</script>
<style type="text/css">
#player {
    position: absolute;
    width: 100% !important;
    height: 100% !important;
}
</style>
<style type="text/css">
body,td,th {
	color: #0F0;
}
body {
	background-color: #000;
}
a:link {
	color: #0FC;
}
a:visited {
	color: #3F6;
}
a:hover {
	color: #09F;
}
a:active {
	color: #009;
}
</style>
</head>
<body>
<div id="player"></div>
<script>
jwplayer("player").setup({
playlist: <?php echo trim($json_data); ?>,
	// https://docs.jwplayer.com/players/reference/setup-options -> Possible values:
    stretching: "uniform",
    controls: true,
    displaytitle: true,
    fullscreen: "true",
    height: "100%",
    width: "100%",
    fallback: false,
    repeat: true,
    autostart: false, 
    //primary: "flash",
	primary: "hls",
    //primary: "html5",
    aspectratio: "16:9",
    renderCaptionsNatively: false,
    abouttext: "Albdroid",
    aboutlink: "http://albdroid.al/",
    mute: false,

skin: {
	name: "glow",
    active: "#fc0303",
    inactive: "#0F0",
    background: "transparent",
	text: "#0F0",
    icons: "#0F0",
    iconsActive: "#0F0",
    timeslider: {
    progress: "none"
    }
},
    logo: {
        file: 'https://png.kodi.al/tv/albdroid/logo_bar.png',
        position: 'control-bar',
        margin: '270',
        hide: 'false'
    },
    autostart: false,
    repeat: true,
    androidhls: true,
});
</script>
</body>
</html>