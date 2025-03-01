<?php
$servername = "localhost";
$username = "vemite5_ai";
$password = "]Rl2!vy+8W3~";
$database = "vemite5_ai";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
} else {
    echo "✅ Successfully connected to the database!";
}

$conn->close();
?>
