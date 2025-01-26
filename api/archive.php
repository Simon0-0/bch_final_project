<?php
include 'db_connection.php';

$type = $_GET['type']; // 'documents', 'equipment', 'tasks', 'suppliers'

$allowed_types = ['documents', 'equipment', 'tasks', 'suppliers'];

if (in_array($type, $allowed_types)) {
    $query = "SELECT * FROM $type WHERE status = 'archived'";
    $result = mysqli_query($conn, $query);

    $archived_items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $archived_items[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($archived_items);
} else {
    http_response_code(400);
    echo json_encode(["error" => "Invalid request type"]);
}
?>
