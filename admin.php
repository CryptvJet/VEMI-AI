<?php
$servername = "localhost";
$username = "vemite5_ai";
$password = "]Rl2!vy+8W3~";
$database = "vemite5_ai";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle adding/editing responses
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

        // Remove from unanswered questions after training
        $stmt = $conn->prepare("DELETE FROM messages WHERE user_message = ?");
        $stmt->bind_param("s", $user_message);
        $stmt->execute();
        $stmt->close();
    }
}

// Handle deleting unanswered questions
if (isset($_GET["delete_unanswered"])) {
    $delete_id = intval($_GET["delete_unanswered"]);
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit;
}

// Handle deleting trained responses
if (isset($_GET["delete"])) {
    $delete_id = intval($_GET["delete"]);
    $stmt = $conn->prepare("DELETE FROM responses WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit;
}

// Handle editing trained responses
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["edit_bot_response"]) && isset($_POST["response_id"])) {
    $edit_bot_response = trim($_POST["edit_bot_response"]);
    $response_id = intval($_POST["response_id"]);

    if (!empty($edit_bot_response)) {
        $stmt = $conn->prepare("UPDATE responses SET bot_response = ? WHERE id = ?");
        $stmt->bind_param("si", $edit_bot_response, $response_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin.php");
        exit;
    }
}

// Search functionality for unanswered questions
$unanswered_search_query = isset($_GET["unanswered_search"]) ? trim($_GET["unanswered_search"]) : "";

// Pagination for Unanswered Questions
$unanswered_limit = 10;
$unanswered_page = isset($_GET['unanswered_page']) ? max(1, intval($_GET['unanswered_page'])) : 1;
$unanswered_offset = ($unanswered_page - 1) * $unanswered_limit;

$unanswered_where_clause = "";
$unanswered_search_param = [];

// If there's a search query, filter results
if (!empty($unanswered_search_query)) {
    $unanswered_where_clause = "AND user_message LIKE ?";
    $unanswered_search_param[] = "%$unanswered_search_query%";
}

// Fetch unanswered questions with pagination
$unanswered_stmt = $conn->prepare("
    SELECT id, user_message 
    FROM messages 
    WHERE bot_response = 'I don\'t know yet!'
    $unanswered_where_clause
    ORDER BY created_at DESC
    LIMIT $unanswered_limit OFFSET $unanswered_offset
");

if (!empty($unanswered_search_param)) {
    $unanswered_stmt->bind_param("s", ...$unanswered_search_param);
}
$unanswered_stmt->execute();
$unanswered_result = $unanswered_stmt->get_result();
$unanswered_stmt->close();

// Get Total Unanswered Questions Count
$total_unanswered_query = "SELECT COUNT(*) AS total FROM messages WHERE bot_response = 'I don\'t know yet!' $unanswered_where_clause";
$stmt = $conn->prepare($total_unanswered_query);

if (!empty($unanswered_search_param)) {
    $stmt->bind_param("s", ...$unanswered_search_param);
}
$stmt->execute();
$total_unanswered_result = $stmt->get_result();
$total_unanswered_row = $total_unanswered_result->fetch_assoc();
$total_unanswered = $total_unanswered_row['total'];
$total_unanswered_pages = ceil($total_unanswered / $unanswered_limit);
$stmt->close();

// Search functionality for trained responses
$responses_search_query = isset($_GET["responses_search"]) ? trim($_GET["responses_search"]) : "";

// Pagination for Trained Responses
$responses_limit = 10;
$responses_page = isset($_GET['responses_page']) ? max(1, intval($_GET['responses_page'])) : 1;
$responses_offset = ($responses_page - 1) * $responses_limit;

$responses_where_clause = "";
$responses_search_param = [];

// If there's a search query, filter results
if (!empty($responses_search_query)) {
    $responses_where_clause = "AND (user_message LIKE ? OR bot_response LIKE ?)";
    $responses_search_param[] = "%$responses_search_query%";
    $responses_search_param[] = "%$responses_search_query%";
}

// Fetch trained responses (Only "Master" responses) with pagination
$responses_stmt = $conn->prepare("
    SELECT * FROM responses 
    WHERE response_type = 'Master'
    $responses_where_clause
    ORDER BY created_at DESC
    LIMIT $responses_limit OFFSET $responses_offset
");

if (!empty($responses_search_param)) {
    $responses_stmt->bind_param("ss", ...$responses_search_param);
}
$responses_stmt->execute();
$responses_result = $responses_stmt->get_result();
$responses_stmt->close();

// Get Total Trained Responses Count
$total_responses_query = "SELECT COUNT(*) AS total FROM responses WHERE response_type = 'Master' $responses_where_clause";
$stmt = $conn->prepare($total_responses_query);

if (!empty($responses_search_param)) {
    $stmt->bind_param("ss", ...$responses_search_param);
}
$stmt->execute();
$total_responses_result = $stmt->get_result();
$total_responses_row = $total_responses_result->fetch_assoc();
$total_responses = $total_responses_row['total'];
$total_responses_pages = ceil($total_responses / $responses_limit);
$stmt->close();

// Search functionality for session logs
$search_query = isset($_GET["search"]) ? trim($_GET["search"]) : "";

// Pagination for Session Logs and User Tracking Logs
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$where_clause = "";
$search_param = [];

// If there's a search query, filter results
if (!empty($search_query)) {
    $where_clause = "WHERE session_id LIKE ? OR ip_address LIKE ? OR user_agent LIKE ?";
    $search_param[] = "%$search_query%";
    $search_param[] = "%$search_query%";
    $search_param[] = "%$search_query%";
}

// Get Total Session Logs Count
$total_query = "SELECT COUNT(*) AS total FROM (
    SELECT session_id, ip_address, user_agent, created_at FROM session_logs
    UNION ALL
    SELECT null AS session_id, ip_address, user_agent, created_at FROM user_tracking
) AS combined_logs $where_clause";
$stmt = $conn->prepare($total_query);

if (!empty($search_param)) {
    $stmt->bind_param("sss", ...$search_param);
}
$stmt->execute();
$total_result = $stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_sessions = $total_row['total'];
$total_pages = ceil($total_sessions / $limit);
$stmt->close();

// Fetch Paginated Session Logs and User Tracking Logs
$sessions_stmt = $conn->prepare("
    SELECT * FROM (
        SELECT session_id, ip_address, user_agent, created_at FROM session_logs
        UNION ALL
        SELECT null AS session_id, ip_address, user_agent, created_at FROM user_tracking
    ) AS combined_logs
    $where_clause
    ORDER BY created_at DESC
    LIMIT $limit OFFSET $offset
");

if (!empty($search_param)) {
    $sessions_stmt->bind_param("sss", ...$search_param);
}
$sessions_stmt->execute();
$sessions_result = $sessions_stmt->get_result();
$sessions_stmt->close();
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
        .btn:hover { background