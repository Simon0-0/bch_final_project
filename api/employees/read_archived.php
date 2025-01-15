<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM Employees WHERE archived = 1";
$stmt = $db->prepare($query);
$stmt->execute();

$num = $stmt->rowCount();

if ($num > 0) {
    $employees_arr = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $employee_item = [
            "employee_id" => $employee_id,
            "name" => $name,
            "email" => $email,
            "position" => $position,
            "role_id" => $role_id,
            "created_at" => $created_at,
            "updated_at" => $updated_at
        ];

        array_push($employees_arr, $employee_item);
    }

    http_response_code(200);
    echo json_encode(["message" => "Archived employees retrieved successfully.", "data" => $employees_arr]);
} else {
    http_response_code(404);
    echo json_encode(["message" => "No archived employees found."]);
}
?>
