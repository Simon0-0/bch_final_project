<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once "../../config/database.php";

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM Equipment WHERE archived = 0";
$stmt = $db->prepare($query);
$stmt->execute();

$num = $stmt->rowCount();

if ($num > 0) {
    $equipment_arr = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $equipment_item = [
            "equipment_id" => $equipment_id,
            "name" => $name,
            "description" => $description,
            "status" => $status,
            "location" => $location,
            "created_at" => $created_at,
            "updated_at" => $updated_at
        ];

        array_push($equipment_arr, $equipment_item);
    }

    http_response_code(200);
    echo json_encode(["message" => "Equipment retrieved successfully.", "data" => $equipment_arr]);
} else {
    http_response_code(404);
    echo json_encode(["message" => "No equipment found."]);
}
?>
