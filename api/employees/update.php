<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";

// Get the JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Debugging: Output the received data
if (!$data) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid JSON payload.", "received_data" => $data]);
    exit();
}

if (empty($data['employee_id'])) {
    http_response_code(400);
    echo json_encode(["message" => "Missing employee_id.", "received_data" => $data]);
    exit();
}

if (empty($data['username']) && empty($data['email']) && empty($data['password']) &&
    empty($data['position']) && !isset($data['role_id']) && !isset($data['archived'])) {
    http_response_code(400);
    echo json_encode(["message" => "No fields to update provided.", "received_data" => $data]);
    exit();
}

// Continue with the update process
$database = new Database();
$db = $database->getConnection();

$query = "UPDATE Employees SET ";
$params = [];

if (!empty($data['username'])) {
    $query .= "name = :username, ";
    $params[':username'] = $data['username'];
}

if (!empty($data['email'])) {
    $query .= "email = :email, ";
    $params[':email'] = $data['email'];
}

if (!empty($data['password'])) {
    $query .= "password_hash = :password_hash, ";
    $params[':password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
}

if (!empty($data['position'])) {
    $query .= "position = :position, ";
    $params[':position'] = $data['position'];
}

if (isset($data['role_id'])) {
    $query .= "role_id = :role_id, ";
    $params[':role_id'] = $data['role_id'];
}

if (isset($data['archived'])) {
    $query .= "archived = :archived, ";
    $params[':archived'] = $data['archived'];
}

$query = rtrim($query, ", ") . " WHERE employee_id = :employee_id";
$params[':employee_id'] = $data['employee_id'];

$stmt = $db->prepare($query);

// Bind parameters dynamically
foreach ($params as $param => $value) {
    $stmt->bindValue($param, $value);
}

// Execute the query
if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(["message" => "Employee updated successfully."]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Failed to update employee."]);
}
?>
