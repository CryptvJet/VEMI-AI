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
    error_log("Database connection failed: " . $conn->connect_error);
    echo json_encode(["response" => "Database connection failed."]);
    exit;
}

// Function to log user interactions
function logUserInteraction($conn, $session_id, $ip_address, $browser_version) {
    $stmt = $conn->prepare("INSERT INTO user_tracking (session_id, ip_address, browser_version, interaction_time) VALUES (?, ?, ?, NOW())");
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }

    $stmt->bind_param("sss", $session_id, $ip_address, $browser_version);

    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        $stmt->close();
        return false;
    }

    $stmt->close();
    return true;
}

// Log user interaction on initial page load
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["init_chat"])) {
    if (!isset($_SESSION["interaction_logged"])) {
        $_SESSION["interaction_logged"] = true;

        // Default browser version to 'unknown' if not provided
        $browser_version = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        if (logUserInteraction($conn, $session_id, $ip_address, $browser_version)) {
            error_log("User interaction logged on page load.");
        } else {
            error_log("Failed to log user interaction on page load.");
        }

        // Send the initial greeting message
        $greeting_message = "Heyy, how are you today?!";
        $stmt = $conn->prepare("INSERT INTO session_logs (session_id, ip_address, user_message, bot_response, created_at) VALUES (?, ?, '', ?, NOW())");
        $stmt->bind_param("sss", $session_id, $ip_address, $greeting_message);
        $stmt->execute();
        $stmt->close();

        echo json_encode(["response" => $greeting_message, "greeting" => true]);
        exit;
    }
}

// Capture and log user interactions via POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input["action"]) && $input["action"] == "log_interaction") {
        $data = $input['data'];
        if (logUserInteraction($conn, $session_id, $ip_address, $data['browser_version'])) {
            echo json_encode(["response" => "User interaction logged."]);
        } else {
            echo json_encode(["response" => "Failed to log user interaction."]);
        }
        exit;
    }

    // Handle "End Chat" Request
    if (isset($_POST["end_chat"])) {
        $stmt = $conn->prepare("INSERT INTO session_logs (session_id, ip_address, user_message, bot_response, created_at) VALUES (?, ?, 'User ended chat', 'Chat session ended.', NOW())");
        $stmt->bind_param("ss", $session_id, $ip_address);
        $stmt->execute();
        $stmt->close();

        session_destroy();
        echo json_encode(["response" => "Chat session ended."]);
        exit;
    }

    // Handle "Reload Chat" Request
    if (isset($_POST["reset_chat"])) {
        $stmt = $conn->prepare("INSERT INTO session_logs (session_id, ip_address, user_message, bot_response, created_at) VALUES (?, ?, 'User refreshed chat', 'Chat reset.', NOW())");
        $stmt->bind_param("ss", $session_id, $ip_address);
        $stmt->execute();
        $stmt->close();

        session_destroy();
        echo json_encode(["response" => "Chat reset."]);
        exit;
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
        // No trained response found - perform web scraping
        $command = escapeshellcmd("python3 py/scrape.py '$user_message'");
        $output = shell_exec($command);

        // Decode the JSON output from the Python script
        $search_results = json_decode($output, true);

        // Generate response using spaCy
        $command = escapeshellcmd("python3 py/nlp.py '$user_message' '$output'");
        $response_output = shell_exec($command);
        $bot_response = $response_output;

        // Log unanswered question for training
        $stmt = $conn->prepare("INSERT IGNORE INTO messages (user_message, bot_response, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $user_message, $bot_response);
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
}
?>