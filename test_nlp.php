<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set("log_errors", 1);
ini_set("error_log", "debug.log");

$user_message = "Tell me about the latest AI news";

// Execute the Python script for web scraping
$command = escapeshellcmd("python3 py/scrape.py '$user_message'");
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
        $search_results = json_decode($output, true);

        // Ensure search results are valid JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(["response" => "Invalid JSON from scrape.py", "error_output" => json_last_error_msg()]);
            exit;
        }

        // Execute the Python script for NLP response generation
        $search_results_json = json_encode($search_results, JSON_UNESCAPED_SLASHES);
        $command = "python3 py/nlp.py " . escapeshellarg($user_message) . " " . escapeshellarg($search_results_json);
        $process = proc_open($command, $descriptorspec, $pipes);

        if (is_resource($process)) {
            // Close stdin
            fclose($pipes[0]);

            // Read the output from stdout
            $response_output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            // Read any errors from stderr
            $error_output = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            // Close the process
            $return_value = proc_close($process);

            // Log the command and outputs
            error_log("Command: $command");
            error_log("Output: $response_output");
            error_log("Error Output: $error_output");
            error_log("Return Value: $return_value");

            // Check for errors
            if ($return_value !== 0) {
                echo json_encode(["response" => "Error executing command.", "error_output" => $error_output]);
            } else {
                // Print the response
                echo json_encode(["response" => $response_output], JSON_PRETTY_PRINT);
            }
        } else {
            error_log("Could not open process for command: $command");
            echo json_encode(["response" => "Could not open process."]);
        }
    }
} else {
    error_log("Could not open process for command: $command");
    echo json_encode(["response" => "Could not open process."]);
}
?>