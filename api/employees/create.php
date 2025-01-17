<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";
include_once "../../config/auth.php";

$data = json_decode(file_get_contents("php://input"), true);

// Validate JSON payload
if (!$data) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid JSON payload."]);
    exit();
}

// Validate required fields
if (empty($data['name']) || empty($data['email']) || empty($data['password']) || empty($data['role_id'])) {
    http_response_code(400);
    echo json_encode(["message" => "Missing required fields."]);
    exit();
}

// Create a new database connection
$database = new Database();
$db = $database->getConnection();

if ($user->role_id > 2 && $user->employee_id !== $data['assigned_to']) {
    http_response_code(403); // Forbidden
    echo json_encode(["message" => "Access denied."]);
    exit();
}


// Insert employee query
$query = "INSERT INTO Employees (name, email, password_hash, position, role_id, archived) 
          VALUES (:name, :email, :password_hash, :position, :role_id, :archived)";
$stmt = $db->prepare($query);

// Bind parameters
$stmt->bindParam(':name', $data['name']);
$stmt->bindParam(':email', $data['email']);
$stmt->bindParam(':password_hash', password_hash($data['password'], PASSWORD_DEFAULT));
$stmt->bindParam(':position', $data['position']);
$stmt->bindParam(':role_id', $data['role_id']);
$stmt->bindValue(':archived', 0, PDO::PARAM_INT); // New employees are not archived

// Execute query
if ($stmt->execute()) {
    http_response_code(201); // Created
    echo json_encode(["message" => "Employee created successfully."]);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(["message" => "Failed to create employee."]);
}
?>
