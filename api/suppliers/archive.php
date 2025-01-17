<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";
include_once "../../config/auth.php";
// Ensure the request method is PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(400);
    echo json_encode(["error" => "Invalid request method. Only PUT is allowed."]);
    exit;
}

// Read the raw input
$rawInput = file_get_contents("php://input");

// Check if input is empty
if (!$rawInput) {
    http_response_code(400);
    echo json_encode(["error" => "Empty request body."]);
    exit;
}

// Decode JSON input
$data = json_decode($rawInput, true);

// Check if decoding was successful
if ($data === null) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid JSON format."]);
    exit;
}

// Validate supplier_id
if (empty($data['supplier_id']) || !is_numeric($data['supplier_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid or missing supplier ID."]);
    exit;
}

try {
    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();

    if ($user->role_id > 2 && $user->employee_id !== $data['assigned_to']) {
        http_response_code(403); // Forbidden
        echo json_encode(["message" => "Access denied."]);
        exit();
    }

    // Prepare the SQL query
    $query = "UPDATE Suppliers SET archived = 1 WHERE supplier_id = :supplier_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':supplier_id', $data['supplier_id'], PDO::PARAM_INT);

    // Execute the update query
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(["message" => "Supplier archived successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Database update failed."]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
