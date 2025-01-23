<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";
include_once "../../config/auth.php";

$database = new Database();
$db = $database->getConnection();

// Check user authentication
require_once "../../config/verify_token.php"; // ✅ Ensure user verification
$user = verifyToken(); // ✅ Fetch user from token

// Decode JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($user->role_id)) {
    http_response_code(403);
    echo json_encode(["message" => "Access denied. User not authenticated."]);
    exit();
}

// Ensure employee_id is provided and at least one field to update
if (!empty($data['employee_id']) && (!empty($data['name']) || !empty($data['email']) || !empty($data['phone']) || !empty($data['position']) || !empty($data['role_id']))) {
    
    if ($user->role_id > 2) { // ✅ Restrict access to only Admin & Manager
        http_response_code(403);
        echo json_encode(["message" => "Access denied."]);
        exit();
    }

    // ✅ Match the actual column names in your database
    $query = "UPDATE Employees SET ";
    $params = [];

    if (!empty($data['name'])) {
        $query .= "name = :name, ";
        $params[':name'] = $data['name'];
    }
    if (!empty($data['email'])) {
        $query .= "email = :email, ";
        $params[':email'] = $data['email'];
    }
    if (!empty($data['phone'])) { // ✅ Ensure column exists in DB
        $query .= "phone = :phone, ";
        $params[':phone'] = $data['phone'];
    }
    if (!empty($data['position'])) {
        $query .= "position = :position, ";
        $params[':position'] = $data['position'];
    }
    if (!empty($data['role_id'])) {
        $query .= "role_id = :role_id, ";
        $params[':role_id'] = $data['role_id'];
    }

    // Remove trailing comma
    $query = rtrim($query, ", ") . " WHERE employee_id = :employee_id";
    $params[':employee_id'] = $data['employee_id'];

    $stmt = $db->prepare($query);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(["message" => "Employee updated successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to update employee."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete or invalid data."]);
}
?>
