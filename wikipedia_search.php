<?php
header('Content-Type: application/json');

function searchWikipedia($query) {
    $url = "https://en.wikipedia.org/w/api.php?action=query&list=search&srsearch=" . urlencode($query) . "&format=json";
    $response = file_get_contents($url);
    if ($response === FALSE) {
        return ["error" => "Error fetching data from Wikipedia."];
    }
    $data = json_decode($response, true);
    if (isset($data['query']['search']) && count($data['query']['search']) > 0) {
        $results = [];
        foreach ($data['query']['search'] as $result) {
            $title = $result['title'];
            $snippet = strip_tags($result['snippet']);
            $pageUrl = "https://en.wikipedia.org/wiki/" . urlencode($title);
            $results[] = ["title" => $title, "snippet" => $snippet, "url" => $pageUrl];
        }
        return ["results" => $results];
    } else {
        return ["error" => "No results found on Wikipedia."];
    }
}

$query = isset($_GET['query']) ? trim($_GET['query']) : '';
if (!empty($query)) {
    echo json_encode(searchWikipedia($query));
} else {
    echo json_encode(["error" => "No query provided."]);
}
?>