<?php
header("Content-Type: application/json");

// Database connection
$conn = new mysqli("localhost", "vemite5_ai", "]Rl2!vy+8W3~", "vemite5-ai");

if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Get transcription data
$input = json_decode(file_get_contents("php://input"), true);
$transcriptionText = trim($input["transcription"] ?? "");

if (empty($transcriptionText)) {
    echo json_encode(["response" => "Transcription cannot be empty."]);
    exit;
}

// Save transcription in database
$stmt = $conn->prepare("INSERT INTO transcriptions (text) VALUES (?)");
$stmt->bind_param("s", $transcriptionText);
$stmt->execute();
$transcription_id = $stmt->insert_id;
$stmt->close();

// Return response with transcription ID for linking
echo json_encode([
    "response" => "Transcription saved.",
    "id" => $transcription_id
]);

$conn->close();
?>
