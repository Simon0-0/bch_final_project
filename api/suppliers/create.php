<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once "../../config/database.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['name']) && !empty($data['contact_name']) && !empty($data['phone_number']) && !empty($data['email'])) {
    $database = new Database();
    $db = $database->getConnection();

    $query = "INSERT INTO Suppliers (name, contact_name, phone_number, email) VALUES (:name, :contact_name, :phone_number, :email)";
    $stmt = $db->prepare($query);

    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':contact_name', $data['contact_name']);
    $stmt->bindParam(':phone_number', $data['phone_number']);
    $stmt->bindParam(':email', $data['email']);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(["message" => "Supplier created successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to create supplier."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete data."]);
}
?>
