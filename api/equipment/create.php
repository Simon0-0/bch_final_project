<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";

$data = json_decode(file_get_contents("php://input"), true);

// Validate JSON payload
if (!$data) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid JSON payload."]);
    exit();
}

// Validate required fields
if (empty($data['name']) || empty($data['description']) || empty($data['status']) || empty($data['location'])) {
    http_response_code(400);
    echo json_encode(["message" => "Missing required fields: name, description, status, or location."]);
    exit();
}

// Create a new database connection
$database = new Database();
$db = $database->getConnection();

// Assign values to variables
$name = $data['name'];
$description = $data['description'];
$status = $data['status'];
$location = $data['location'];
$assigned_to = $data['assigned_to'] ?? null; // Optional
$supplier_id = $data['supplier_id'] ?? null; // Optional
$created_at = date('Y-m-d H:i:s');
$updated_at = date('Y-m-d H:i:s');

// Insert equipment query
$query = "INSERT INTO Equipment (name, description, status, location, assigned_to, supplier_id, created_at, updated_at) 
          VALUES (:name, :description, :status, :location, :assigned_to, :supplier_id, :created_at, :updated_at)";
$stmt = $db->prepare($query);

// Bind parameters using variables
$stmt->bindParam(':name', $name);
$stmt->bindParam(':description', $description);
$stmt->bindParam(':status', $status);
$stmt->bindParam(':location', $location);
$stmt->bindParam(':assigned_to', $assigned_to, PDO::PARAM_INT); // Optional
$stmt->bindParam(':supplier_id', $supplier_id, PDO::PARAM_INT); // Optional
$stmt->bindParam(':created_at', $created_at);
$stmt->bindParam(':updated_at', $updated_at);

// Execute query
if ($stmt->execute()) {
    http_response_code(201); // Created
    echo json_encode(["message" => "Equipment created successfully."]);
} else {
    http_response_code(500); // Internal Server Error
    error_log(print_r($stmt->errorInfo(), true)); // Log error details
    echo json_encode(["message" => "Failed to create equipment."]);
}
?>
