<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";

header('Content-Type: application/json'); // ✅ Ensure JSON output

$database = new Database();
$db = $database->getConnection();

// ✅ Capture JSON input
$data = json_decode(file_get_contents("php://input"), true);
$document_id = $data['document_id'] ?? null;

// ✅ Debug log
file_put_contents("debug_document_archive.txt", print_r($data, true));

if (!$document_id) {
    http_response_code(400);
    echo json_encode(["message" => "Document ID is required."]);
    exit();
}

// ✅ Update Query to Archive Document
$query = "UPDATE Documents SET archived = 1 WHERE document_id = :document_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":document_id", $document_id);

if ($stmt->execute()) {
    echo json_encode(["message" => "Document archived successfully."]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Failed to archive document."]);
}
?>
