<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include Composer's autoload file
require 'vendor/autoload.php';

use donatj\UserAgent\UserAgentParser;

// Database connection details
$servername = "localhost";
$username = "vemite5_ai";
$password = "]Rl2!vy+8W3~";
$database = "vemite5_ai";

// Establish database connection
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die(json_encode(["response" => "Database connection failed: " . $conn->connect_error]));
}

// Function to get the user's IP address
function getUserIpAddr() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// Function to get browser details
function getBrowserDetails($user_agent) {
    $parser = new UserAgentParser();
    $result = $parser->parse($user_agent);
    return [
        'browser_name' => $result->browser(),
        'browser_version' => $result->browserVersion(),
        'os' => $result->platform(),
    ];
}

// Collect user data
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
$browser_details = getBrowserDetails($user_agent);
$browser_name = $browser_details['browser_name'];
$browser_version = $browser_details['browser_version'];
$os = $browser_details['os'];
$window_width = isset($_GET['window_width']) ? intval($_GET['window_width']) : 0;
$window_height = isset($_GET['window_height']) ? intval($_GET['window_height']) : 0;
$screen_width = isset($_GET['screen_width']) ? intval($_GET['screen_width']) : 0;
$screen_height = isset($_GET['screen_height']) ? intval($_GET['screen_height']) : 0;
$referrer = isset($_POST['referrer']) ? $_POST['referrer'] : 'unknown';
$current_url = $_SERVER['REQUEST_URI'];
$latitude = isset($_GET['latitude']) ? floatval($_GET['latitude']) : 0.0;
$longitude = isset($_GET['longitude']) ? floatval($_GET['longitude']) : 0.0;
$ip_address = getUserIpAddr();

// Prepare the SQL statement
$sql = "INSERT INTO user_tracking (user_agent, browser_name, browser_version, os, window_width, window_height, screen_width, screen_height, referrer, current_url, latitude, longitude, ip_address, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die(json_encode(["response" => "Prepare failed: " . $conn->error]));
}

// Bind parameters
$stmt->bind_param("ssssiiiiissss", $user_agent, $browser_name, $browser_version, $os, $window_width, $window_height, $screen_width, $screen_height, $referrer, $current_url, $latitude, $longitude, $ip_address);

// Execute the statement
if ($stmt->execute()) {
    echo json_encode(["response" => "User tracking information logged successfully."]);
} else {
    echo json_encode(["response" => "Failed to log user tracking information."]);
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>