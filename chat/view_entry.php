<?php
$servername = "localhost";
$username = "vemite5_ai";
$password = "]Rl2!vy+8W3~";
$database = "vemite5_ai";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Database connection failed");
}

$session_id = $_GET['session_id'] ?? '';

// ✅ Fetch all messages from the selected session ID
$stmt = $conn->prepare("
    SELECT ip_address, user_message, bot_response, created_at 
    FROM session_logs 
    WHERE session_id = ?
    ORDER BY created_at ASC
");
$stmt->bind_param("s", $session_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// ✅ Fetch the IP address once for the session
$ip_address = "";
if ($row = $result->fetch_assoc()) {
    $ip_address = $row['ip_address'];
}
$result->data_seek(0); // Reset pointer for table display
?>

<!DOCTYPE html>
<html>
<head>
    <title>Chat Transcript - <?php echo htmlspecialchars($session_id); ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h2 { border-bottom: 2px solid #007bff; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
    </style>
</head>
<body>
    <h2>Chat Transcript</h2>
    <p><strong>Session ID:</strong> <?php echo htmlspecialchars($session_id); ?></p>
    <p><strong>IP Address:</strong> <?php echo htmlspecialchars($ip_address); ?></p>

    <table>
        <tr>
            <th>Timestamp</th>
            <th>User Message</th>
            <th>Vemi's Response</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['created_at']; ?></td>
            <td>
                <?php 
                echo htmlspecialchars($row['user_message']); 
                if ($row['user_message'] === "User ended chat" || $row['user_message'] === "User refreshed chat") {
                    echo " 🔴"; // Add a red indicator for session closures
                }
                ?>
            </td>
            <td><?php echo htmlspecialchars($row['bot_response']); ?></td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>

<?php $conn->close(); ?>
