<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Collect user data
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
$browser_name = 'unknown'; // Placeholder, use appropriate method to detect browser name
$browser_version = 'unknown'; // Placeholder, use appropriate method to detect browser version
$os = 'unknown'; // Placeholder, use appropriate method to detect OS
$window_width = isset($_GET['window_width']) ? intval($_GET['window_width']) : 0;
$window_height = isset($_GET['window_height']) ? intval($_GET['window_height']) : 0;
$screen_width = isset($_GET['screen_width']) ? intval($_GET['screen_width']) : 0;
$screen_height = isset($_GET['screen_height']) ? intval($_GET['screen_height']) : 0;
$referrer = $_SERVER['HTTP_REFERER'] ?? 'unknown';
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