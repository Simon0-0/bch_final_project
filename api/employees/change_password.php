<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";
include_once "../../config/auth.php";

$database = new Database();
$db = $database->getConnection();

require_once "../../config/verify_token.php"; 
$user = verifyToken(); 

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['currentPassword']) || empty($data['newPassword'])) {
    http_response_code(400);
    echo json_encode(["message" => "Missing required fields."]);
    exit();
}

// Get current password hash from database
$query = "SELECT password_hash FROM Employees WHERE employee_id = :employee_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':employee_id', $user->employee_id);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row || !password_verify($data['currentPassword'], $row['password_hash'])) {
    http_response_code(403);
    echo json_encode(["message" => "Incorrect current password."]);
    exit();
}

// Update password
$newPasswordHash = password_hash($data['newPassword'], PASSWORD_DEFAULT);
$updateQuery = "UPDATE Employees SET password_hash = :password_hash WHERE employee_id = :employee_id";
$updateStmt = $db->prepare($updateQuery);
$updateStmt->bindParam(':password_hash', $newPasswordHash);
$updateStmt->bindParam(':employee_id', $user->employee_id);

if ($updateStmt->execute()) {
    echo json_encode(["message" => "Password updated successfully."]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Failed to update password."]);
}
?>
