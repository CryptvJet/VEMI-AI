<?php
header('Content-Type: text/html');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "vemite5_ai";
$password = "]Rl2!vy+8W3~";
$database = "vemite5_ai";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

function getTableStructure($conn, $tableName) {
    $structure = [];
    $result = $conn->query("DESCRIBE $tableName");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $structure[] = $row;
        }
    } else {
        echo "<p>Error describing table $tableName: " . $conn->error . "</p>";
    }
    return $structure;
}

function getTableData($conn, $tableName) {
    $data = [];
    $result = $conn->query("SELECT * FROM $tableName");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    } else {
        echo "<p>Error selecting data from table $tableName: " . $conn->error . "</p>";
    }
    return $data;
}

$tables = [];
$result = $conn->query("SHOW TABLES");
if ($result) {
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
} else {
    die("Error showing tables: " . $conn->error);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Structure and Data</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <h1>Database Structure and Data</h1>
    <?php if (empty($tables)): ?>
        <p class="error">No tables found in the database.</p>
    <?php else: ?>
        <?php foreach ($tables as $table): ?>
            <h2>Table: <?php echo htmlspecialchars($table); ?></h2>
            <h3>Structure</h3>
            <table>
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Type</th>
                        <th>Null</th>
                        <th>Key</th>
                        <th>Default</th>
                        <th>Extra</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $structure = getTableStructure($conn, $table);
                    if (!empty($structure)):
                        foreach ($structure as $column):
                    ?>
                            <tr>
                                <td><?php echo htmlspecialchars($column['Field']); ?></td>
                                <td><?php echo htmlspecialchars($column['Type']); ?></td>
                                <td><?php echo htmlspecialchars($column['Null']); ?></td>
                                <td><?php echo htmlspecialchars($column['Key']); ?></td>
                                <td><?php echo htmlspecialchars($column['Default']); ?></td>
                                <td><?php echo htmlspecialchars($column['Extra']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="error">No structure found or error describing table.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <h3>Data</h3>
            <table>
                <thead>
                    <tr>
                        <?php foreach ($structure as $column): ?>
                            <th><?php echo htmlspecialchars($column['Field']); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $data = getTableData($conn, $table);
                    if (!empty($data)):
                        foreach ($data as $row):
                    ?>
                            <tr>
                                <?php foreach ($structure as $column): ?>
                                    <td><?php echo htmlspecialchars($row[$column['Field']]); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo count($structure); ?>" class="error">No data found or error selecting data.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php $conn->close(); ?>
</body>
</html>