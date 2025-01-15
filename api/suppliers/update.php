<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['supplier_id']) && (!empty($data['name']) || !empty($data['contact_name']) || !empty($data['phone_number']) || !empty($data['email']))) {
    $database = new Database();
    $db = $database->getConnection();

    $query = "UPDATE Suppliers SET ";
    $params = [];

    if (!empty($data['name'])) {
        $query .= "name = :name, ";
        $params[':name'] = $data['name'];
    }

    if (!empty($data['contact_name'])) {
        $query .= "contact_name = :contact_name, ";
        $params[':contact_name'] = $data['contact_name'];
    }

    if (!empty($data['phone_number'])) {
        $query .= "phone_number = :phone_number, ";
        $params[':phone_number'] = $data['phone_number'];
    }

    if (!empty($data['email'])) {
        $query .= "email = :email, ";
        $params[':email'] = $data['email'];
    }

    $query = rtrim($query, ", ") . " WHERE supplier_id = :supplier_id";
    $params[':supplier_id'] = $data['supplier_id'];

    $stmt = $db->prepare($query);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(["message" => "Supplier updated successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to update supplier."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete or invalid data."]);
}
?>
