<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";
include_once "../../config/auth.php"; // Include role-check helper functions

$data = json_decode(file_get_contents("php://input"), true);

// Check if the required fields are provided
if (empty($data['equipment_id']) || empty($data['user_id']) || empty($data['role_id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "Incomplete data."]);
    exit();
}

$equipment_id = $data['equipment_id'];
$user_id = $data['user_id'];
$user_role_id = $data['role_id'];

$database = new Database();
$db = $database->getConnection();

// Fetch the owner of the equipment
$query = "SELECT assigned_to FROM Equipment WHERE equipment_id = :equipment_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':equipment_id', $equipment_id, PDO::PARAM_INT);
$stmt->execute();

$item_owner_id = $stmt->fetchColumn();

// Check if the equipment exists
if ($item_owner_id === false) {
    http_response_code(404); // Not Found
    echo json_encode(["message" => "Equipment not found."]);
    exit();
}

// Check permissions
if (!isUserResponsible($user_id, $item_owner_id) && !checkUserPermissions($user_role_id, 2)) {
    http_response_code(403); // Forbidden
    echo json_encode(["message" => "Access denied. You do not have permission to archive this equipment."]);
    exit();
}

// Proceed with archiving the equipment
$query = "UPDATE Equipment SET archived = 1 WHERE equipment_id = :equipment_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':equipment_id', $equipment_id, PDO::PARAM_INT);

if ($stmt->execute()) {
    http_response_code(200); // OK
    echo json_encode(["message" => "Equipment archived successfully."]);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(["message" => "Failed to archive equipment."]);
}
?>
