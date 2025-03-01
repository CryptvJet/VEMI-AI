<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set("log_errors", 1);
ini_set("error_log", "debug.log");

$servername = "localhost";
$username = "vemite5_ai";
$password = "]Rl2!vy+8W3~";
$database = "vemite5_ai";

session_start();

// Generate a session ID if not set
if (!isset($_SESSION['session_id'])) {
    $_SESSION['session_id'] = bin2hex(random_bytes(8));
}

$session_id = $_SESSION['session_id'];
$ip_address = $_SERVER['REMOTE_ADDR'];

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    echo json_encode(["response" => "Database connection failed."]);
    exit;
}

// Function to log user interactions
function logUserInteraction($conn, $data) {
    $stmt = $conn->prepare("INSERT INTO user_tracking (user_agent, browser_name, browser_version, os, window_width, window_height, screen_width, screen_height, referrer, current_url, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssiiiisss", $data['user_agent'], $data['browser_name'], $data['browser_version'], $data['os'], $data['window_width'], $data['window_height'], $data['screen_width'], $data['screen_height'], $data['referrer'], $data['current_url'], $data['ip_address']);
    $stmt->execute();
    $stmt->close();
}

// Capture and log user interactions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]) && $_POST["action"] == "log_interaction") {
    $data = json_decode(file_get_contents('php://input'), true)['data'];
    logUserInteraction($conn, $data);
    exit;
}

// Handle "End Chat" Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["end_chat"])) {
    $stmt = $conn->prepare("INSERT INTO session_logs (session_id, ip_address, user_message, bot_response, created_at) VALUES (?, ?, 'User ended chat', 'Chat session ended.', NOW())");
    $stmt->bind_param("ss", $session_id, $ip_address);
    $stmt->execute();
    $stmt->close();

    session_destroy();
    echo json_encode(["response" => "Chat session ended."]);
    exit;
}

// Handle "Reload Chat" Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["reset_chat"])) {
    $stmt = $conn->prepare("INSERT INTO session_logs (session_id, ip_address, user_message, bot_response, created_at) VALUES (?, ?, 'User refreshed chat', 'Chat reset.', NOW())");
    $stmt->bind_param("ss", $session_id, $ip_address);
    $stmt->execute();
    $stmt->close();

    session_destroy();
    echo json_encode(["response" => "Chat reset."]);
    exit;
}

// Send the initial greeting message on page load
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["init_chat"])) {
    if (!isset($_SESSION["greeting_shown"])) {
        $_SESSION["greeting_shown"] = true;

        $greeting_message = "Heyy, how are you today?!";
        
        // Log greeting message in session logs
        $stmt = $conn->prepare("INSERT INTO session_logs (session_id, ip_address, user_message, bot_response, created_at) VALUES (?, ?, '', ?, NOW())");
        $stmt->bind_param("sss", $session_id, $ip_address, $greeting_message);
        $stmt->execute();
        $stmt->close();

        echo json_encode(["response" => $greeting_message, "greeting" => true]);
        exit;
    }
}

// Get User Message & Normalize Input
$user_message = trim($_POST["message"] ?? '');
if ($user_message === '') {
    echo json_encode(["response" => "I don't know yet!"]);
    exit;
}

$user_message = strtolower($user_message);
$user_message = preg_replace("/[^a-z0-9\s]/", "", $user_message);

// Check for trained responses in the database
$stmt = $conn->prepare("SELECT bot_response FROM responses WHERE user_message = ? ORDER BY FIELD(response_type, 'Master', 'AI') LIMIT 1");
$stmt->bind_param("s", $user_message);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $bot_response = $row['bot_response'];
} else {
    // No trained response found
    $bot_response = "I don't know yet!";
    
    // Log unanswered question for training
    $stmt = $conn->prepare("INSERT IGNORE INTO messages (user_message, bot_response, created_at) VALUES (?, 'I don\'t know yet!', NOW())");
    $stmt->bind_param("s", $user_message);
    $stmt->execute();
    $stmt->close();
}

// Save session log
$stmt = $conn->prepare("INSERT INTO session_logs (session_id, ip_address, user_message, bot_response, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("ssss", $session_id, $ip_address, $user_message, $bot_response);
$stmt->execute();
$stmt->close();

// Ensure "How can I help you?" appears once per response cycle
if (!isset($_SESSION["help_shown"])) {
    $_SESSION["help_shown"] = false;
}
$_SESSION["last_message_time"] = time();

echo json_encode(["response" => $bot_response, "show_help" => true]);

$conn->close();
exit;
?>