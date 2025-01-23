<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";
include_once "../../config/auth.php";

$database = new Database();
$db = $database->getConnection();
require_once "../../config/verify_token.php"; 
$user = verifyToken(); 

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['equipment_id']) || empty($data['name']) || empty($data['status'])) {
    http_response_code(400);
    echo json_encode(["message" => "Missing required fields."]);
    exit();
}

// ✅ Fetch Equipment Owner
$query = "SELECT assigned_to FROM Equipment WHERE equipment_id = :equipment_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':equipment_id', $data['equipment_id']);
$stmt->execute();
$equipment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$equipment) {
    http_response_code(404);
    echo json_encode(["message" => "Equipment not found."]);
    exit();
}

// ✅ Check Permissions
if ($user->role_id > 2 && $user->employee_id !== $equipment['assigned_to']) {
    http_response_code(403);
    echo json_encode(["message" => "Access denied. You are not authorized to edit this equipment."]);
    exit();
}

// ✅ Update Equipment
$query = "UPDATE Equipment SET name = :name, description = :description, status = :status, location = :location WHERE equipment_id = :equipment_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':name', $data['name']);
$stmt->bindParam(':description', $data['description']);
$stmt->bindParam(':status', $data['status']);
$stmt->bindParam(':location', $data['location']);
$stmt->bindParam(':equipment_id', $data['equipment_id']);

if ($stmt->execute()) {
    echo json_encode(["message" => "Equipment updated successfully."]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Failed to update equipment."]);
}
?>
