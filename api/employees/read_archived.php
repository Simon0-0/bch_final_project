<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";
include_once "../../config/auth.php";

$database = new Database();
$db = $database->getConnection();


// Fetch archived employees
$query = "SELECT * FROM Employees WHERE archived = 1";
$stmt = $db->prepare($query);
$stmt->execute();

$employees_arr = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $employees_arr[] = $row;
}

// Return response
http_response_code(200);
echo json_encode($employees_arr);
?>
