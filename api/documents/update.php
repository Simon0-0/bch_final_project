<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['document_id']) && (!empty($data['title']) || !empty($data['content']))) {
    $database = new Database();
    $db = $database->getConnection();

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

    $query = rtrim($query, ", ") . " WHERE document_id = :document_id";
    $params[':document_id'] = $data['document_id'];

    $stmt = $db->prepare($query);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(["message" => "Document updated successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to update document."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete or invalid data."]);
}
?>
