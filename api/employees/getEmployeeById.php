<?php
// Include database connection
require_once '../../config/database.php';
include_once "../../config/cors.php";

// Get employee ID from the query parameter
$employee_id = isset($_GET['employee_id']) ? $_GET['employee_id'] : die(json_encode(["message" => "Employee ID not provided"]));

// Create a new database connection
$database = new Database();
$db = $database->getConnection();

// Query to get employee by ID
$query = "SELECT * FROM Employees WHERE employee_id = :employee_id LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
$stmt->execute();

// Fetch the employee data
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if employee exists
if ($employee) {
    // Return employee details in JSON format
    echo json_encode(['employee' => $employee]);
} else {
    // Return error if employee not found
    echo json_encode(["message" => "Employee not found"]);
}
?>
