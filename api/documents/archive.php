<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['document_id'])) {
    $database = new Database();
    $db = $database->getConnection();

    $query = "UPDATE Documents SET archived = 1 WHERE document_id = :document_id";
    $stmt = $db->prepare($query);

    $stmt->bindParam(':document_id', $data['document_id'], PDO::PARAM_INT);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(["message" => "Document archived successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to archive document."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Invalid document ID."]);
}
?>
