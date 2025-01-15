<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['title']) && !empty($data['description']) && !empty($data['status']) && !empty($data['priority']) && !empty($data['due_date'])) {
    $database = new Database();
    $db = $database->getConnection();

    $query = "INSERT INTO Tasks (title, description, status, priority, due_date) VALUES (:title, :description, :status, :priority, :due_date)";
    $stmt = $db->prepare($query);

    $stmt->bindParam(':title', $data['title']);
    $stmt->bindParam(':description', $data['description']);
    $stmt->bindParam(':status', $data['status']);
    $stmt->bindParam(':priority', $data['priority']);
    $stmt->bindParam(':due_date', $data['due_date']);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(["message" => "Task created successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to create task."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete data."]);
}
?>
