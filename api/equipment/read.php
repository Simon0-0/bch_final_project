<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";
require_once "../../config/verify_token.php"; // Verify the token
include_once "../../config/auth.php";

// ✅ Verify token and fetch user data
$user = verifyToken(); // Decoded user data from the token

$database = new Database();
$db = $database->getConnection();

$query = "SELECT 
            equipment_id, 
            name, 
            description, 
            serial_number, 
            purchase_date, 
            warranty_expiration, 
            warranty_status, 
            status, 
            location, 
            assigned_to, 
            supplier_id, 
            image_path, 
            created_at, 
            updated_at, 
            archived, 
            image, 
            image_data 
          FROM Equipment 
          WHERE archived = 0";

$stmt = $db->prepare($query);
$stmt->execute();

$num = $stmt->rowCount();

if ($num > 0) {
    $equipment_arr = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        // ✅ Ensure Full Image URL
        $fullImagePath = !empty($image_path) ? "http://localhost/bch_final_project/" . $image_path : null;

        // ✅ Convert BLOB images to Base64 if they exist
        $imageBase64 = !empty($image) ? base64_encode($image) : null;
        $imageDataBase64 = !empty($image_data) ? base64_encode($image_data) : null;

        $equipment_item = [
            "equipment_id" => $equipment_id,
            "name" => $name,
            "description" => $description,
            "serial_number" => $serial_number,
            "purchase_date" => $purchase_date,
            "warranty_expiration" => $warranty_expiration,
            "warranty_status" => $warranty_status,
            "status" => $status,
            "location" => $location,
            "assigned_to" => $assigned_to,
            "supplier_id" => $supplier_id,
            "image_path" => $fullImagePath,  // ✅ Corrected Full Image URL
            "created_at" => $created_at,
            "updated_at" => $updated_at,
            "archived" => $archived,
            "image" => $imageBase64, // ✅ Base64 image string
            "image_data" => $imageDataBase64 // ✅ Base64 image string
        ];

        array_push($equipment_arr, $equipment_item);
    }

    http_response_code(200); // OK
    echo json_encode(["message" => "Equipment retrieved successfully.", "data" => $equipment_arr]);
} else {
    http_response_code(404); // Not Found
    echo json_encode(["message" => "No equipment found."]);
}
?>
