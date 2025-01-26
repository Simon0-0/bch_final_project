<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM Tasks WHERE archived = 1";
$stmt = $db->prepare($query);
$stmt->execute();

$tasks_arr = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $tasks_arr[] = $row; // Directly append data
}

// Always return an array
http_response_code(200);
echo json_encode($tasks_arr);
?>
