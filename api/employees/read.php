<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");


include_once "../../config/database.php";

require_once "../../config/verify_token.php"; // Verify the token
$user = verifyToken(); // Fetch user data from the token

$database = new Database();
$db = $database->getConnection();


$query = "SELECT * FROM Employees WHERE archived = 0";
$stmt = $db->prepare($query);

try {
    $stmt->execute();
    $num = $stmt->rowCount();

    if ($num > 0) {
        $employees_arr = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $employee_item = [
                "employee_id" => $employee_id,
                "name" => $name,
                "email" => $email,
                "position" => $position,
                "role_id" => $role_id,
                "created_at" => $created_at,
                "updated_at" => $updated_at
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
