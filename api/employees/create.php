<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once "../../config/database.php";

$data = json_decode(file_get_contents("php://input"), true);

// Debugging: Check if JSON is valid
if (!$data) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid JSON payload.", "received_data" => $data]);
    exit();
}

// Debugging: Check for employee_id
if (empty($data['employee_id'])) {
    http_response_code(400);
    echo json_encode(["message" => "Missing employee_id.", "received_data" => $data]);
    exit();
}

// Debugging: Ensure at least one field to update is provided
if (empty($data['username']) && empty($data['email']) && empty($data['password']) &&
    empty($data['position']) && !isset($data['role_id']) && !isset($data['archived'])) {
    http_response_code(400);
    echo json_encode(["message" => "No fields to update provided.", "received_data" => $data]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Dynamic query construction
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

// Finalize query
$query = rtrim($query, ", ") . " WHERE employee_id = :employee_id";
$params[':employee_id'] = $data['employee_id'];

$stmt = $db->prepare($query);

// Bind parameters
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

// Execute query
if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(["message" => "Employee updated successfully."]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Failed to update employee."]);
}
?>
