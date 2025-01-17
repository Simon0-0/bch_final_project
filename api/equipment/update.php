<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";
include_once "../../config/auth.php";
require_once "../../config/verify_token.php";

$data = json_decode(file_get_contents("php://input"), true);

$user = verifyToken();
if (!$user) {
    http_response_code(403);
    echo json_encode(["message" => "Unauthorized access. Invalid token."]);
    exit();
}

if (empty($data['equipment_id']) || 
    (empty($data['name']) && empty($data['description']) && empty($data['status']) && empty($data['location']))) {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete or invalid data.", "received" => $data]);
    exit();
}

if (!isset($data['assigned_to'])) {
    http_response_code(400);
    echo json_encode(["message" => "Missing assigned_to field.", "received" => $data]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

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
    $stmt->bindValue($key, htmlspecialchars(strip_tags($value)));
}

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(["message" => "Equipment updated successfully."]);
} else {
    $errorInfo = $stmt->errorInfo();
    http_response_code(500);
    echo json_encode(["message" => "Failed to update equipment.", "error" => $errorInfo[2]]);
}
?>
