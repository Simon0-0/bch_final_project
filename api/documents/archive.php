<?php
header("Access-Control-Allow-Origin: *");

header("Access-Control-Allow-Methods: POST, OPTIONS");

header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['document_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing document_id"]);
    exit();
}

$database_host = "localhost";
$database_user = "root";
$database_pass = "root"; // Change if necessary
$database_name = "bch_final_project";

$conn = new mysqli($database_host, $database_user, $database_pass, $database_name);

if ($conn->connect_error) {
    file_put_contents("debug.log", "DB Connection Failed: " . $conn->connect_error . "\n", FILE_APPEND);
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}
$document_id = $data['document_id'];
$sql = "UPDATE documents SET archived = 1 WHERE document_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $document_id);

if ($stmt->execute()) {
    file_put_contents("debug.log", "Document $document_id archived successfully.\n", FILE_APPEND);
    echo json_encode(["message" => "Document archived successfully"]);
} else {
    file_put_contents("debug.log", "SQL Error: " . $stmt->error . "\n", FILE_APPEND);
    echo json_encode(["error" => "Failed to archive document"]);
}

$stmt->close();
$conn->close();
?>
