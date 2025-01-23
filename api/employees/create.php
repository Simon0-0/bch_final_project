<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";
include_once "../../config/auth.php";

$database = new Database();
$db = $database->getConnection();

// Check user authentication
require_once "../../config/verify_token.php"; 
$user = verifyToken(); 

// Decode JSON input
$data = json_decode(file_get_contents("php://input"), true);

// ✅ Debugging: Log received data
error_log(print_r($data, true));

if (!isset($user->role_id) || $user->role_id > 1) { 
    http_response_code(403);
    echo json_encode(["message" => "Access denied. Only Admins can create employees."]);
    exit();
}

// ✅ Ensure all required fields are present
if (empty($data['name']) || empty($data['email']) || empty($data['phone']) || empty($data['position']) || empty($data['role_id']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(["message" => "Missing required fields."]);
    exit();
}

// ✅ Hash password securely
$password_hash = password_hash($data['password'], PASSWORD_DEFAULT);

// ✅ Insert new employee
$query = "INSERT INTO Employees (name, email, phone, position, role_id, password_hash) 
          VALUES (:name, :email, :phone, :position, :role_id, :password_hash)";

$stmt = $db->prepare($query);
$stmt->bindParam(':name', $data['name']);
$stmt->bindParam(':email', $data['email']);
$stmt->bindParam(':phone', $data['phone']);
$stmt->bindParam(':position', $data['position']);
$stmt->bindParam(':role_id', $data['role_id']);
$stmt->bindParam(':password_hash', $password_hash); // ✅ Hash password before storing

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode(["message" => "Employee created successfully."]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Failed to create employee."]);
}
?>
