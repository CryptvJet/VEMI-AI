<?php
header('Content-Type: application/json');

function searchDuckDuckGo($query) {
    $url = "https://api.duckduckgo.com/?q=" . urlencode($query) . "&format=json";
    $response = file_get_contents($url);
    if ($response === FALSE) {
        return ["error" => "Error fetching data from DuckDuckGo."];
    }
    $data = json_decode($response, true);
    if (isset($data['Abstract']) && !empty($data['Abstract'])) {
        return ["result" => $data['Abstract']];
    } else {
        return ["error" => "No results found on DuckDuckGo."];
    }
}

$query = isset($_GET['query']) ? trim($_GET['query']) : '';
if (!empty($query)) {
    echo json_encode(searchDuckDuckGo($query));
} else {
    echo json_encode(["error" => "No query provided."]);
}
?>