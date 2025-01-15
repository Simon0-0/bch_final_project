<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";
include_once "../../config/auth.php";
require_once "../../config/verify_token.php";

$data = json_decode(file_get_contents("php://input"), true);

// Verify token and fetch user data
$user = verifyToken();

if (!empty($data['equipment_id']) && 
    (!empty($data['name']) || !empty($data['description']) || !empty($data['status']) || !empty($data['location']))) {

    $database = new Database();
    $db = $database->getConnection();

    // Check if user has permission to update
    if ($user->role_id > 2 && $user->employee_id !== $data['assigned_to']) {
        http_response_code(403); // Forbidden
        echo json_encode(["message" => "Access denied."]);
        exit();
    }

    // Proceed with the update
    $query = "UPDATE Equipment SET ";
    $params = [];

    if (!empty($data['name'])) {
        $query .= "name = :name, ";
        $params[':name'] = $data['name'];
    }

    if (!empty($data['description'])) {
        $query .= "description = :description, ";
        $params[':description'] = $data['description'];
    }

    if (!empty($data['status'])) {
        $query .= "status = :status, ";
        $params[':status'] = $data['status'];
    }

    if (!empty($data['location'])) {
        $query .= "location = :location, ";
        $params[':location'] = $data['location'];
    }

    $query = rtrim($query, ", ") . " WHERE equipment_id = :equipment_id";
    $params[':equipment_id'] = $data['equipment_id'];

    $stmt = $db->prepare($query);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    if ($stmt->execute()) {
        http_response_code(200); // OK
        echo json_encode(["message" => "Equipment updated successfully."]);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(["message" => "Failed to update equipment."]);
    }
} else {
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "Incomplete or invalid data."]);
}
?>
