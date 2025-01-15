<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";
include_once "../../config/auth.php"; // Role-check helper functions
require_once "../../config/verify_token.php"; // JWT token verification

// Verify and fetch user data from the token
$user = verifyToken();

$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (empty($data['equipment_id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "Equipment ID is required."]);
    exit();
}

$equipment_id = intval($data['equipment_id']);
if (!$equipment_id) {
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "Invalid Equipment ID."]);
    exit();
}
$user_id = intval($user->employee_id); // Extracted from token
$user_role_id = intval($user->role_id); // Extracted from token

$database = new Database();
$db = $database->getConnection();

try {
    // Fetch the assigned owner of the equipment
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

    // Archive the equipment
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
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    error_log("Database error: " . $e->getMessage());
    echo json_encode(["message" => "An internal server error occurred."]);
}
?>
