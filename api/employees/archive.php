<?php
// api/users/archive.php
require_once '../../config/cors.php';
include_once "../../config/database.php";

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['employee_id'])) {
    $database = new Database();
    $db = $database->getConnection();

    $query = "UPDATE Employees SET archived = 1 WHERE employee_id = :employee_id";

    $stmt = $db->prepare($query);

    $stmt->bindParam(':employee_id', $data['employee_id']);

    if ($stmt->execute()) {
        http_response_code(200); // 200 OK
        echo json_encode(["message" => "User archived successfully."]);
    } else {
        http_response_code(500); // 500 Internal Server Error
        echo json_encode(["message" => "Failed to archive user."]);
    }
} else {
    http_response_code(400); // 400 Bad Request
    echo json_encode(["message" => "Invalid user ID."]);
}
?>
