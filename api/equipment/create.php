<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once "../../config/database.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['name']) && !empty($data['description']) && !empty($data['status']) && !empty($data['location'])) {
    $database = new Database();
    $db = $database->getConnection();

    $query = "INSERT INTO Equipment (name, description, status, location) VALUES (:name, :description, :status, :location)";
    $stmt = $db->prepare($query);

    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':description', $data['description']);
    $stmt->bindParam(':status', $data['status']);
    $stmt->bindParam(':location', $data['location']);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(["message" => "Equipment created successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to create equipment."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete data."]);
}
?>
