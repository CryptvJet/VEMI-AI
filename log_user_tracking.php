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

// Function to log user tracking information
function logUserTracking($conn) {
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $browser_name = 'unknown'; // Placeholder, use appropriate method to detect browser name
    $browser_version = 'unknown'; // Placeholder, use appropriate method to detect browser version
    $os = 'unknown'; // Placeholder, use appropriate method to detect OS
    $window_width = 0; // Placeholder, use JavaScript to get the actual value
    $window_height = 0; // Placeholder, use JavaScript to get the actual value
    $screen_width = 0; // Placeholder, use JavaScript to get the actual value
    $screen_height = 0; // Placeholder, use JavaScript to get the actual value
    $referrer = $_SERVER['HTTP_REFERER'] ?? 'unknown';
    $current_url = $_SERVER['REQUEST_URI'];
    $latitude = 0.0; // Placeholder, use JavaScript to get the actual value
    $longitude = 0.0; // Placeholder, use JavaScript to get the actual value
    $ip_address = getUserIpAddr();

    $stmt = $conn->prepare("INSERT INTO user_tracking (user_agent, browser_name, browser_version, os, window_width, window_height, screen_width, screen_height, referrer, current_url, latitude, longitude, ip_address, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }

    // Correct number of parameters
    $stmt->bind_param("ssssiiiiisssds", $user_agent, $browser_name, $browser_version, $os, $window_width, $window_height, $screen_width, $screen_height, $referrer, $current_url, $latitude, $longitude, $ip_address);

    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        $stmt->close();
        return false;
    }

    $stmt->close();
    return true;
}

// Log user tracking information
if (logUserTracking($conn)) {
    echo json_encode(["response" => "User tracking information logged successfully."]);
} else {
    echo json_encode(["response" => "Failed to log user tracking information."]);
}

$conn->close();
?>