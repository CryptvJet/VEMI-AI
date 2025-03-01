<?php
$servername = "localhost";
$username = "vemite5_ai";
$password = "]Rl2!vy+8W3~";
$database = "vemite5_ai";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Database connection failed");
}

// Function to log user interactions
function logUserInteraction($conn, $data) {
    $stmt = $conn->prepare("INSERT INTO user_tracking (user_agent, browser_name, browser_version, os, window_width, window_height, screen_width, screen_height, referrer, current_url, latitude, longitude, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssiiiissdds", $data['user_agent'], $data['browser_name'], $data['browser_version'], $data['os'], $data['window_width'], $data['window_height'], $data['screen_width'], $data['screen_height'], $data['referrer'], $data['current_url'], $data['latitude'], $data['longitude'], $data['ip_address']);
    $stmt->execute();
    $stmt->close();
}

// Capture and log user interactions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]) && $_POST["action"] == "log_interaction") {
    $data = json_decode(file_get_contents('php://input'), true);
    logUserInteraction($conn, $data);
    exit;
}

// Handle other interactions (e.g., chat messages)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["message"])) {
    $user_message = trim($_POST["message"]);
    $response = ""; // Generate AI response here
    echo json_encode(["response" => $response]);
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>AI Chat</title>
    <script src="/js/captureUserData.js"></script>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        #chatBox { width: 100%; height: 300px; border: 1px solid #ddd; overflow-y: scroll; margin-bottom: 10px; padding: 10px; }
        #message { width: calc(100% - 22px); padding: 10px; }
        #sendButton { padding: 10px; }
    </style>
</head>
<body>
    <h2>AI Chat</h2>
    <div id="chatBox"></div>
    <input type="text" id="message" placeholder="Type your message here...">
    <button id="sendButton">Send</button>

    <script>
        document.getElementById('sendButton').addEventListener('click', function() {
            var message = document.getElementById('message').value;
            if (message.trim() === "") return;

            // Append user's message to chatBox
            var chatBox = document.getElementById('chatBox');
            var userMessage = document.createElement('div');
            userMessage.textContent = "You: " + message;
            chatBox.appendChild(userMessage);

            // Send message to server
            fetch('ai-chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: message })
            })
            .then(response => response.json())
            .then(data => {
                // Append AI's response to chatBox
                var aiMessage = document.createElement('div');
                aiMessage.textContent = "AI: " + data.response;
                chatBox.appendChild(aiMessage);
                chatBox.scrollTop = chatBox.scrollHeight; // Scroll to bottom
            });

            document.getElementById('message').value = ""; // Clear input
        });

        // Log user interaction when the page loads
        window.onload = function() {
            getUserData(); // Call the function from captureUserData.js to log user data
        }
    </script>
</body>
</html>