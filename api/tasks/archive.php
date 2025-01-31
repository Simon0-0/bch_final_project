<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";
include_once "../../config/auth.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['task_id'])) {
    $database = new Database();
    $db = $database->getConnection();

    if ($user->role_id > 2 && $user->employee_id !== $data['assigned_to']) {
        http_response_code(403); // Forbidden
        echo json_encode(["message" => "Access denied."]);
        exit();
    }

    $query = "UPDATE Tasks SET archived = 1 WHERE task_id = :task_id";
    $stmt = $db->prepare($query);

    $stmt->bindParam(':task_id', $data['task_id'], PDO::PARAM_INT);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(["message" => "Task archived successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to archive task."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Invalid task ID."]);
}
?>
