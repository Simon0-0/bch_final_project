<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once "../../config/database.php";

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM Tasks WHERE archived = 1";
$stmt = $db->prepare($query);
$stmt->execute();

$num = $stmt->rowCount();

if ($num > 0) {
    $tasks_arr = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $task_item = [
            "task_id" => $task_id,
            "title" => $title,
            "description" => $description,
            "status" => $status,
            "priority" => $priority,
            "due_date" => $due_date,
            "created_at" => $created_at,
            "updated_at" => $updated_at
        ];

        array_push($tasks_arr, $task_item);
    }

    http_response_code(200);
    echo json_encode(["message" => "Archived tasks retrieved successfully.", "data" => $tasks_arr]);
} else {
    http_response_code(404);
    echo json_encode(["message" => "No archived tasks found."]);
}
?>
