<?php
$servername = "localhost";
$username = "vemite5_ai";
$password = "]Rl2!vy+8W3~";
$database = "vemite5_ai";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Database connection failed");
}

// ✅ Handle adding/editing responses
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["user_message"]) && isset($_POST["bot_response"])) {
    $user_message = trim($_POST["user_message"]);
    $bot_response = trim($_POST["bot_response"]);

    if (!empty($user_message) && !empty($bot_response)) {
        $stmt = $conn->prepare("INSERT INTO responses (user_message, bot_response, response_type) 
                                VALUES (?, ?, 'Master') 
                                ON DUPLICATE KEY UPDATE bot_response = ?, response_type = 'Master'");
        $stmt->bind_param("sss", $user_message, $bot_response, $bot_response);
        $stmt->execute();
        $stmt->close();

        // ✅ Remove from unanswered questions after training
        $stmt = $conn->prepare("DELETE FROM messages WHERE user_message = ?");
        $stmt->bind_param("s", $user_message);
        $stmt->execute();
        $stmt->close();
    }
}

// ✅ Handle deleting unanswered questions
if (isset($_GET["delete_unanswered"])) {
    $delete_id = intval($_GET["delete_unanswered"]);
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit;
}

// ✅ Handle deleting trained responses
if (isset($_GET["delete"])) {
    $delete_id = intval($_GET["delete"]);
    $stmt = $conn->prepare("DELETE FROM responses WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit;
}

// ✅ Fetch unanswered questions
$unanswered_result = $conn->query("
    SELECT id, user_message 
    FROM messages 
    WHERE bot_response = 'I don\'t know yet!'
    ORDER BY created_at DESC
");

// ✅ Fetch trained responses (Only "Master" responses)
$responses_result = $conn->query("
    SELECT * FROM responses 
    WHERE response_type = 'Master' 
    ORDER BY created_at DESC
");

// ✅ Search functionality for session logs
$search_query = isset($_GET["search"]) ? trim($_GET["search"]) : "";

// ✅ Pagination for Session Logs
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$where_clause = "";
$search_param = [];

// ✅ If there's a search query, filter results
if (!empty($search_query)) {
    $where_clause = "WHERE session_id LIKE ? OR ip_address LIKE ?";
    $search_param[] = "%$search_query%";
    $search_param[] = "%$search_query%";
}

// ✅ Get Total Session Logs Count
$total_query = "SELECT COUNT(DISTINCT session_id) AS total FROM session_logs $where_clause";
$stmt = $conn->prepare($total_query);

if (!empty($search_param)) {
    $stmt->bind_param("ss", ...$search_param);
}
$stmt->execute();
$total_result = $stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_sessions = $total_row['total'];
$total_pages = ceil($total_sessions / $limit);
$stmt->close();

// ✅ Fetch Paginated Session Logs
$sessions_query = "
    SELECT DISTINCT session_id, ip_address, MAX(created_at) AS last_activity
    FROM session_logs
    $where_clause
    GROUP BY session_id, ip_address
    ORDER BY last_activity DESC
    LIMIT $limit OFFSET $offset
";

$stmt = $conn->prepare($sessions_query);
if (!empty($search_param)) {
    $stmt->bind_param("ss", ...$search_param);
}
$stmt->execute();
$sessions_result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel - Manage Responses & Logs</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        .btn { padding: 5px 10px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; border: none; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        .delete-btn { background: red; }
        .delete-btn:hover { background: darkred; }
        .pagination { margin-top: 10px; text-align: center; }
        .pagination a { padding: 5px 10px; margin: 2px; text-decoration: none; background: #007bff; color: white; border-radius: 5px; }
        .pagination a.disabled { background: gray; pointer-events: none; }
    </style>
</head>
<body>

    <h2>Manage AI Responses</h2>

    <!-- ✅ Add New Response -->
    <h3>Add New Response</h3>
    <form method="POST">
        <input type="text" name="user_message" placeholder="User Message" required>
        <input type="text" name="bot_response" placeholder="Bot Response" required>
        <button type="submit">Save Response</button>
    </form>

    <!-- ✅ Unanswered Questions -->
    <h3>Unanswered Questions (Needs Training)</h3>
    <table>
        <tr>
            <th>User Message</th>
            <th>Train Response</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $unanswered_result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['user_message']); ?></td>
            <td>
                <form method="POST">
                    <input type="hidden" name="user_message" value="<?php echo htmlspecialchars($row['user_message']); ?>">
                    <input type="text" name="bot_response" placeholder="Enter response" required>
                    <button type="submit">Save</button>
                </form>
            </td>
            <td>
                <a href="?delete_unanswered=<?php echo $row['id']; ?>" class="delete-btn btn">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>

    <!-- ✅ Trained Responses -->
    <h3>Trained Responses</h3>
    <table>
        <tr>
            <th>User Message</th>
            <th>Bot Response</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $responses_result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['user_message']); ?></td>
            <td><?php echo htmlspecialchars($row['bot_response']); ?></td>
            <td>
                <a href="?delete=<?php echo $row['id']; ?>" class="btn delete-btn">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>

    <!-- ✅ Session Logs -->
    <h2>Session Logs</h2>
    <form method="GET">
        <input type="text" name="search" placeholder="Search Session ID or IP" value="<?php echo htmlspecialchars($search_query); ?>">
        <button type="submit" class="btn">Search</button>
        <a href="admin.php" class="btn delete-btn">Clear</a>
    </form>

    <table>
        <tr>
            <th>Session ID</th>
            <th>IP Address</th>
            <th>Last Activity</th>
        </tr>
        <?php while ($row = $sessions_result->fetch_assoc()) { ?>
        <tr>
            <td><a href="view_entry.php?session_id=<?php echo $row['session_id']; ?>" target="_blank"><?php echo htmlspecialchars($row['session_id']); ?></a></td>
            <td><?php echo htmlspecialchars($row['ip_address']); ?></td>
            <td><?php echo $row['last_activity']; ?></td>
        </tr>
        <?php } ?>
    </table>

    <!-- ✅ Pagination Controls -->
    <div class="pagination">
        <a href="?page=<?php echo $page - 1; ?>" class="<?php echo ($page <= 1) ? 'disabled' : ''; ?>">◀ Previous</a>
        <span>Page <?php echo $page . " of " . $total_pages; ?></span>
        <a href="?page=<?php echo $page + 1; ?>" class="<?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">Next ▶</a>
    </div>

</body>
</html>

<?php $conn->close(); ?>
