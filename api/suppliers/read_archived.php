<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM Suppliers WHERE archived = 1";
$stmt = $db->prepare($query);
$stmt->execute();

$suppliers_arr = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $suppliers_arr[] = $row; // Directly append data
}

// Always return an array
http_response_code(200);
echo json_encode($suppliers_arr);
?>
