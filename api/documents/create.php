<?php
// Include necessary files for DB connection and authentication
require_once '../../config/database.php';
require_once '../../config/auth.php'; // Include authentication for user validation
include '../../config/cors.php'; // Include CORS headers

// Ensure request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(["error" => "Invalid request method. Only POST is allowed."]);
    exit;
}

// Read JSON input
$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

// Debug: Log received data
error_log("Received Data: " . print_r($data, true));

// Validate required fields
if (!$data || empty($data['title']) || empty($data['content']) )  {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields: title, content, or status.", "received" => $data]);
    exit;
}


// Create a new database connection
$database = new Database();
$db = $database->getConnection();

// Prepare the query to insert a new document
$query = "INSERT INTO Documents (title, content, created_by, created_at, updated_at)
          VALUES (:title, :content, :created_by, NOW(), NOW())";

// Prepare the statement
$stmt = $db->prepare($query);

// Bind parameters
$stmt->bindParam(':title', $data['title']);
$stmt->bindParam(':content', $data['content']);
$stmt->bindParam(':created_by', $user['user_id']); // Use the authenticated user ID

// Execute the query
if ($stmt->execute()) {
    echo json_encode(["message" => "Document created successfully"]);
} else {
    echo json_encode(["error" => "Failed to create document"]);
}
?>
