<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set("log_errors", 1);
ini_set("error_log", "debug.log");

$query = "Latest news on AI";

// Execute the Python script for web scraping
$command = escapeshellcmd("python3 py/scrape.py '$query'");
$output = shell_exec($command);

// Decode the JSON output from the Python script
$results = json_decode($output, true);

// Print the results
echo json_encode($results, JSON_PRETTY_PRINT);
?>