<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once "../../config/database.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['task_id']) && (!empty($data['title']) || !empty($data['description']) || !empty($data['status']) || !empty($data['priority']) || !empty($data['due_date']))) {
    $database = new Database();
    $db = $database->getConnection();

    $query = "UPDATE Tasks SET ";
    $params = [];

    if (!empty($data['title'])) {
        $query .= "title = :title, ";
        $params[':title'] = $data['title'];
    }

    if (!empty($data['description'])) {
        $query .= "description = :description, ";
        $params[':description'] = $data['description'];
    }

    if (!empty($data['status'])) {
        $query .= "status = :status, ";
        $params[':status'] = $data['status'];
    }

    if (!empty($data['priority'])) {
        $query .= "priority = :priority, ";
        $params[':priority'] = $data['priority'];
    }

    if (!empty($data['due_date'])) {
        $query .= "due_date = :due_date, ";
        $params[':due_date'] = $data['due_date'];
    }

    $query = rtrim($query, ", ") . " WHERE task_id = :task_id";
    $params[':task_id'] = $data['task_id'];

    $stmt = $db->prepare($query);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(["message" => "Task updated successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to update task."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete or invalid data."]);
}
?>
