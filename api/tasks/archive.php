<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ✅ Database Connection
$database_host = "localhost";
$database_user = "root";
$database_pass = "root"; // ✅ Use your correct password
$database_name = "bch_final_project"; // ✅ Ensure this is correct

$conn = new mysqli($database_host, $database_user, $database_pass, $database_name);

if ($conn->connect_error) {
    file_put_contents("debug.log", "DB Connection Failed: " . $conn->connect_error . "\n", FILE_APPEND);
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// ✅ Read the request body
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['task_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing task_id"]);
    exit();
}

$task_id = $data['task_id'];

// ✅ Archive Task by "Soft Deleting" Instead of Changing Status
$sql = "UPDATE tasks SET archived = 1 WHERE task_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $task_id);

if ($stmt->execute()) {
    file_put_contents("debug.log", "✅ Task $task_id archived successfully.\n", FILE_APPEND);
    echo json_encode(["message" => "Task archived successfully"]);
} else {
    file_put_contents("debug.log", "❌ SQL Error: " . $stmt->error . "\n", FILE_APPEND);
    echo json_encode(["error" => "Failed to archive task"]);
}

$stmt->close();
$conn->close();
?>
