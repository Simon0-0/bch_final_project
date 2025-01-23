<?php
require_once '../../config/cors.php';
include_once "../../config/auth.php";
include_once "../../config/database.php";
require_once "../../config/verify_token.php"; 

$user = verifyToken(); // Get user data from token
$database = new Database();
$db = $database->getConnection();

// ✅ Check if Role 3 (Employee) is fetching only themselves
if ($user->role_id > 2) { 
    $query = "SELECT * FROM Employees WHERE archived = 0 AND employee_id = :employee_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':employee_id', $user->employee_id);
} else { 
    // ✅ Role 1 & 2 can fetch all employees
    $query = "SELECT * FROM Employees WHERE archived = 0";
    $stmt = $db->prepare($query);
}

try {
    $stmt->execute();
    $num = $stmt->rowCount();

    if ($num > 0) {
        $employees_arr = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $employee_item = [
                "employee_id" => $row['employee_id'],
                "name" => $row['name'],
                "email" => $row['email'],
                "position" => $row['position'],
                "role_id" => $row['role_id'],
                "created_at" => $row['created_at'],
                "updated_at" => $row['updated_at']
            ];
            array_push($employees_arr, $employee_item);
        }

        http_response_code(200);
        echo json_encode(["message" => "Employees retrieved successfully.", "data" => $employees_arr]);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "No employees found."]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage()]);
}
?>
