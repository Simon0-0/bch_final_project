<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM Equipment WHERE archived = 1";
$stmt = $db->prepare($query);
$stmt->execute();

$equipment_arr = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $equipment_arr[] = $row; // Directly append data
}

// Always return an array
http_response_code(200);
echo json_encode($equipment_arr);
?>
