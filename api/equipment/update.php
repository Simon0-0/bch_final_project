<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once "../../config/database.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['equipment_id']) && (!empty($data['name']) || !empty($data['description']) || !empty($data['status']) || !empty($data['location']))) {
    $database = new Database();
    $db = $database->getConnection();

    $query = "UPDATE Equipment SET ";
    $params = [];

    if (!empty($data['name'])) {
        $query .= "name = :name, ";
        $params[':name'] = $data['name'];
    }

    if (!empty($data['description'])) {
        $query .= "description = :description, ";
        $params[':description'] = $data['description'];
    }

    if (!empty($data['status'])) {
        $query .= "status = :status, ";
        $params[':status'] = $data['status'];
    }

    if (!empty($data['location'])) {
        $query .= "location = :location, ";
        $params[':location'] = $data['location'];
    }

    $query = rtrim($query, ", ") . " WHERE equipment_id = :equipment_id";
    $params[':equipment_id'] = $data['equipment_id'];

    $stmt = $db->prepare($query);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(["message" => "Equipment updated successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to update equipment."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete or invalid data."]);
}
?>
