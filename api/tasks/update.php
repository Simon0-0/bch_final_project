<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";
include_once "../../config/auth.php";

header('Content-Type: application/json'); // ✅ Ensure JSON response

$database = new Database();
$db = $database->getConnection();

// ✅ Enable error logging for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ✅ Capture JSON data
$data = json_decode(file_get_contents("php://input"), true);
$task_id = $data['task_id'] ?? null;
$title = $data['title'] ?? null;
$description = $data['description'] ?? null;
$status = $data['status'] ?? null;
$priority = $data['priority'] ?? null;
$due_date = $data['due_date'] ?? null;
$assigned_to = $data['assigned_to'] ?? null;

// ✅ Debug incoming request
file_put_contents("debug_task_update.txt", print_r($data, true));

// ✅ Ensure required fields exist
if (!$task_id || (!$title && !$description && !$status && !$priority && !$due_date && !$assigned_to)) {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete or invalid data."]);
    exit();
}

// ✅ Ensure user has permission to update the task
if (isset($user) && $user->role_id > 2 && $user->employee_id !== $assigned_to) {
    http_response_code(403);
    echo json_encode(["message" => "Access denied."]);
    exit();
}

// ✅ Build dynamic update query
$query = "UPDATE Tasks SET ";
$params = [];

if (!empty($title)) {
    $query .= "title = :title, ";
    $params[':title'] = $title;
}

if (!empty($description)) {
    $query .= "description = :description, ";
    $params[':description'] = $description;
}

if (!empty($status)) {
    $query .= "status = :status, ";
    $params[':status'] = $status;
}

if (!empty($priority)) {
    $query .= "priority = :priority, ";
    $params[':priority'] = $priority;
}

if (!empty($due_date)) {
    $query .= "due_date = :due_date, ";
    $params[':due_date'] = $due_date;
}

// ✅ Ensure `assigned_to` is always updated
$query .= "assigned_to = :assigned_to, ";
$params[':assigned_to'] = $assigned_to ?? null;

// ✅ Remove last comma and add WHERE clause
$query = rtrim($query, ", ") . " WHERE task_id = :task_id";
$params[':task_id'] = $task_id;

$stmt = $db->prepare($query);

// ✅ Bind values dynamically
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

// ✅ Execute query and return response
if ($stmt->execute()) {
    echo json_encode(["message" => "Task updated successfully.", "assigned_to" => $assigned_to]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Failed to update task."]);
}
?>
