<?php
$servername = "localhost";
$username = "vemite5_ai";
$password = "]Rl2!vy+8W3~";
$database = "vemite5_ai";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Database connection failed");
}

// ✅ Handle batch updates (Save all changes at once)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["save_changes"])) {
    foreach ($_POST["response_id"] as $index => $response_id) {
        $response_id = intval($response_id);
        $new_response = trim($_POST["bot_response"][$index]);
        $new_type = in_array($_POST["response_type"][$index], ["Master", "AI", "UP"]) ? $_POST["response_type"][$index] : "AI";

        if (!empty($new_response)) {
            $stmt = $conn->prepare("UPDATE responses SET bot_response = ?, response_type = ? WHERE id = ?");
            $stmt->bind_param("ssi", $new_response, $new_type, $response_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    header("Location: responses.php");
    exit;
}

// ✅ Handle deleting responses
if (isset($_GET["delete"])) {
    $delete_id = intval($_GET["delete"]);
    $stmt = $conn->prepare("DELETE FROM responses WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("Location: responses.php");
    exit;
}

// ✅ Get search filters
$search_all = isset($_GET["search_all"]) ? trim($_GET["search_all"]) : "";
$search_user = isset($_GET["search_user"]) ? trim($_GET["search_user"]) : "";
$search_bot = isset($_GET["search_bot"]) ? trim($_GET["search_bot"]) : "";
$sort_type = isset($_GET["sort_type"]) && $_GET["sort_type"] == "DESC" ? "DESC" : "ASC";

// ✅ Build query dynamically to fetch responses including `UP`
$query = "SELECT * FROM responses WHERE response_type IN ('Master', 'AI', 'UP')";
$params = [];
$types = "";

if (!empty($search_all)) {
    $query .= " AND (user_message LIKE ? OR bot_response LIKE ?)";
    $params[] = "%$search_all%";
    $params[] = "%$search_all%";
    $types .= "ss";
}
if (!empty($search_user)) {
    $query .= " AND user_message LIKE ?";
    $params[] = "%$search_user%";
    $types .= "s";
}
if (!empty($search_bot)) {
    $query .= " AND bot_response LIKE ?";
    $params[] = "%$search_bot%";
    $types .= "s";
}

// ✅ Sorting alphabetically by type (UP included)
$query .= " ORDER BY FIELD(response_type, 'Master', 'AI', 'UP'), updated_at DESC";

// ✅ Prepare statement
$stmt = $conn->prepare($query);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$responses_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>AI Learning Messages & Responses</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h1 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th:nth-child(1) { width: 27.5%; } /* User Input */
        th:nth-child(2) { width: 50%; } /* Bot Response */
        th:nth-child(3) { width: 12.5%; } /* Type */
        th:nth-child(4) { width: 10%; } /* Actions */
        .btn { padding: 5px 10px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; border: none; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        .delete-btn { background: red; }
        .delete-btn:hover { background: darkred; }
        input, select { padding: 5px; width: 100%; }
        .edit-btn { background: orange; }
        .edit-btn:hover { background: darkorange; }
        .search-box { width: 80%; padding: 5px; }
        .search-btn { padding: 5px 10px; margin-left: 5px; }
    </style>
    <script>
        function enableEdit(id) {
            document.getElementById("response_" + id).style.display = "none";
            document.getElementById("editForm_" + id).style.display = "inline-block";
            document.getElementById("editBtn_" + id).style.display = "none";
        }

        function clearSearch() {
            window.location.href = "responses.php"; // Refreshes page and clears search
        }
    </script>
</head>
<body>

    <h1>AI Learning Messages & Responses</h1>

    <!-- ✅ Search All Messages -->
    <form method="GET">
        <table>
            <tr>
                <th colspan="3">
                    <input type="text" name="search_all" class="search-box" placeholder="Search All Messages/Responses" value="<?php echo htmlspecialchars($search_all); ?>">
                </th>
                <th>
                    <button type="submit" class="btn search-btn">Search</button>
                    <button type="button" class="btn delete-btn" onclick="clearSearch()">Clear All</button>
                </th>
            </tr>
        </table>
    </form>

    <!-- ✅ Table for AI Responses -->
    <form method="GET">
        <table>
            <tr>
                <th>
                    User Input <br>
                    <input type="text" name="search_user" class="search-box" style="width: 75%;" placeholder="Search Inputs" value="<?php echo htmlspecialchars($search_user); ?>">
                    <button type="submit" class="btn search-btn">🔍</button>
                </th>
                <th>
                    Bot Response <br>
                    <input type="text" name="search_bot" class="search-box" placeholder="Search Responses" value="<?php echo htmlspecialchars($search_bot); ?>">
                    <button type="submit" class="btn search-btn">🔍</button>
                </th>
                <th>
                    <a href="?sort_type=<?php echo ($sort_type == "ASC") ? "DESC" : "ASC"; ?>" class="btn">Sort by Type</a>
                </th>
                <th></th>
            </tr>
        </table>
    </form>

    <form method="POST">
        <table>
            <tr>
                <th>User Input</th>
                <th>Bot Response</th>
                <th>Type</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $responses_result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['user_message']); ?></td>
                <td>
                    <span id="response_<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['bot_response']); ?></span>

                    <div id="editForm_<?php echo $row['id']; ?>" style="display: none;">
                        <input type="hidden" name="response_id[]" value="<?php echo $row['id']; ?>">
                        <input type="text" name="bot_response[]" value="<?php echo htmlspecialchars($row['bot_response']); ?>" required>
                    </div>
                </td>
                <td>
                    <select name="response_type[]">
                        <option value="Master" <?php echo ($row['response_type'] == "Master") ? "selected" : ""; ?>>Master</option>
                        <option value="AI" <?php echo ($row['response_type'] == "AI") ? "selected" : ""; ?>>AI</option>
                        <option value="UP" <?php echo ($row['response_type'] == "UP") ? "selected" : ""; ?>>UP</option>
                    </select>
                </td>
                <td>
                    <button type="button" class="btn edit-btn" onclick="enableEdit(<?php echo $row['id']; ?>)">Edit</button>
                    <a href="?delete=<?php echo $row['id']; ?>" class="btn delete-btn">Delete</a>
                </td>
            </tr>
            <?php } ?>
        </table>
        <button type="submit" name="save_changes" class="btn">Save All Changes</button>
    </form>
</body>
</html>

<?php $conn->close(); ?>
