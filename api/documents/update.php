<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";
include_once "../../config/auth.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['document_id']) && (!empty($data['title']) || !empty($data['content']) || isset($data['file_link']))) {
    $database = new Database();
    $db = $database->getConnection();

    // ✅ Ensure user has permission to update
    if ($user->role_id > 2 && $user->employee_id !== $data['assigned_to']) {
        http_response_code(403);
        echo json_encode(["message" => "Access denied."]);
        exit();
    }

    $query = "UPDATE Documents SET ";
    $params = [];

    if (!empty($data['title'])) {
        $query .= "title = :title, ";
        $params[':title'] = $data['title'];
    }

    if (!empty($data['content'])) {
        $query .= "content = :content, ";
        $params[':content'] = $data['content'];
    }

    if (isset($data['file_link'])) { // ✅ Allow `file_link` to be updated
        $query .= "file_link = :file_link, ";
        $params[':file_link'] = $data['file_link'];
    }

    $query = rtrim($query, ", ") . " WHERE document_id = :document_id";
    $params[':document_id'] = $data['document_id'];

    $stmt = $db->prepare($query);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(["message" => "Document updated successfully.", "file_link" => $data['file_link']]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to update document."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete or invalid data."]);
}
?>
