<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once "../../config/database.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['title']) && !empty($data['content']) && !empty($data['created_by'])) {
    $database = new Database();
    $db = $database->getConnection();

    $query = "INSERT INTO Documents (title, content, created_by) VALUES (:title, :content, :created_by)";
    $stmt = $db->prepare($query);

    $stmt->bindParam(':title', $data['title']);
    $stmt->bindParam(':content', $data['content']);
    $stmt->bindParam(':created_by', $data['created_by']);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(["message" => "Document created successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to create document."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete data."]);
}
?>
