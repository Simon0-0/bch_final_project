<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";
include_once "../../config/auth.php";

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM Documents WHERE archived = 1";
$stmt = $db->prepare($query);
$stmt->execute();

$documents_arr = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $documents_arr[] = $row;
}

// Always return a valid JSON array
http_response_code(200);
echo json_encode($documents_arr);
?>
