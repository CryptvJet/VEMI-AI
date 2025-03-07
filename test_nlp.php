<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set("log_errors", 1);
ini_set("error_log", "debug.log");

$user_message = "Tell me about the latest AI news";

// Execute the Python script for web scraping
$command = escapeshellcmd("python3 py/scrape.py '$user_message'");
$output = shell_exec($command);

// Decode the JSON output from the Python script
$search_results = json_decode($output, true);

// Execute the Python script for NLP response generation
$search_results_json = json_encode($search_results);
$command = escapeshellcmd("python3 py/nlp.py '$user_message' '$search_results_json'");
$response_output = shell_exec($command);

// Print the response
echo json_encode(["response" => $response_output], JSON_PRETTY_PRINT);
?>