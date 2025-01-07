<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once "../../config/database.php";

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM Documents WHERE archived = 0";
$stmt = $db->prepare($query);
$stmt->execute();

$num = $stmt->rowCount();

if ($num > 0) {
    $documents_arr = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $document_item = [
            "document_id" => $document_id,
            "title" => $title,
            "content" => $content,
            "created_by" => $created_by,
            "created_at" => $created_at,
            "updated_at" => $updated_at
        ];
        
        array_push($documents_arr, $document_item);
    }
    http_response_code(200);
    echo json_encode(["message" => "Documents retrieved successfully.", "data" => $documents_arr]);
} else {
    http_response_code(404);
    echo json_encode(["message" => "No documents found."]);
}
?>
