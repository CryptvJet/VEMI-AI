<?php
$servername = "localhost";
$username = "vemite5_ai";
$password = "]Rl2!vy+8W3~";
$database = "vemite5_ai";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$data = json_decode(file_get_contents('php://input'), true);

$user_agent = $data['userAgent'];
$browser_name = $data['browserName'];
$browser_version = $data['browserVersion'];
$os = $data['os'];
$window_width = $data['windowWidth'];
$window_height = $data['windowHeight'];
$screen_width = $data['screenWidth'];
$screen_height = $data['screenHeight'];
$referrer = $data['referrer'];
$current_url = $data['currentUrl'];
$latitude = isset($data['latitude']) ? $data['latitude'] : null;
$longitude = isset($data['longitude']) ? $data['longitude'] : null;
$ip_address = $_SERVER['REMOTE_ADDR'];

$stmt = $conn->prepare("INSERT INTO user_tracking (user_agent, browser_name, browser_version, os, window_width, window_height, screen_width, screen_height, referrer, current_url, latitude, longitude, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssiiiissdds", $user_agent, $browser_name, $browser_version, $os, $window_width, $window_height, $screen_width, $screen_height, $referrer, $current_url, $latitude, $longitude, $ip_address);
$stmt->execute();
$stmt->close();
$conn->close();
?>