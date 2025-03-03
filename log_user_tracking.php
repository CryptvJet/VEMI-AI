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
$referrer = $_SERVER['HTTP_REFERER'] ?? 'unknown';
$current_url = $_SERVER['REQUEST_URI'];
$ip_address = getUserIpAddr();

// Prepare the SQL statement
$sql = "INSERT INTO user_tracking (user_agent, referrer, current_url, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die(json_encode(["response" => "Prepare failed: " . $conn->error]));
}

// Bind parameters
$stmt->bind_param("ssss", $user_agent, $referrer, $current_url, $ip_address);

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