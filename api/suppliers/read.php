<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once "../../config/database.php";

require_once "../../config/verify_token.php"; // Verify the token
$user = verifyToken(); // Fetch user data from the token


$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM Suppliers WHERE archived = 0";
$stmt = $db->prepare($query);
$stmt->execute();

$num = $stmt->rowCount();

if ($num > 0) {
    $suppliers_arr = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $supplier_item = [
            "supplier_id" => $supplier_id,
            "name" => $name,
            "contact_name" => $contact_name,
            "phone_number" => $phone_number,
            "email" => $email,
            "created_at" => $created_at,
            "updated_at" => $updated_at
        ];

        array_push($suppliers_arr, $supplier_item);
    }

    http_response_code(200);
    echo json_encode(["message" => "Suppliers retrieved successfully.", "data" => $suppliers_arr]);
} else {
    http_response_code(404);
    echo json_encode(["message" => "No suppliers found."]);
}
?>
