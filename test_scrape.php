<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set("log_errors", 1);
ini_set("error_log", "debug.log");

$query = "Latest news on AI";

// Execute the Python script for web scraping using proc_open
$command = escapeshellcmd("python3 py/scrape.py '$query'");
$descriptorspec = array(
    0 => array("pipe", "r"),  // stdin
    1 => array("pipe", "w"),  // stdout
    2 => array("pipe", "w")   // stderr
);

$process = proc_open($command, $descriptorspec, $pipes);

if (is_resource($process)) {
    // Close stdin
    fclose($pipes[0]);

    // Read the output from stdout
    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    // Read any errors from stderr
    $error_output = stream_get_contents($pipes[2]);
    fclose($pipes[2]);

    // Close the process
    $return_value = proc_close($process);

    // Log the command and outputs
    error_log("Command: $command");
    error_log("Output: $output");
    error_log("Error Output: $error_output");
    error_log("Return Value: $return_value");

    // Check for errors
    if ($return_value !== 0) {
        echo json_encode(["response" => "Error executing command.", "error_output" => $error_output]);
    } else {
        // Decode the JSON output from the Python script
        $results = json_decode($output, true);
        // Print the results
        echo json_encode($results, JSON_PRETTY_PRINT);
    }
} else {
    error_log("Could not open process for command: $command");
    echo json_encode(["response" => "Could not open process."]);
}
?>