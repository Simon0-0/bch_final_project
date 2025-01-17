<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";
include_once "../../config/auth.php";

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
if (!$data || empty($data['title']) || empty($data['content']) || empty($data['status'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields: title, content, or status.", "received" => $data]);
    exit;
}

try {
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    if ($user->role_id > 2 && $user->employee_id !== $data['assigned_to']) {
        http_response_code(403); // Forbidden
        echo json_encode(["message" => "Access denied."]);
        exit();
    }

    // Insert query
    $query = "INSERT INTO Documents (title, content, status, created_at) VALUES (:title, :content, :status, NOW())";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':title', $data['title']);
    $stmt->bindParam(':content', $data['content']);
    $stmt->bindParam(':status', $data['status']);

    // Execute query
    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(["message" => "Document created successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Database error: Unable to create document."]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
