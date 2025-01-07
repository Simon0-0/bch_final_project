<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once "../../config/database.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['equipment_id'])) {
    $database = new Database();
    $db = $database->getConnection();

    $query = "UPDATE Equipment SET archived = 1 WHERE equipment_id = :equipment_id";
    $stmt = $db->prepare($query);

    $stmt->bindParam(':equipment_id', $data['equipment_id'], PDO::PARAM_INT);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(["message" => "Equipment archived successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to archive equipment."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Invalid equipment ID."]);
}
?>
