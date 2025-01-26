<?php
require_once '../../config/database.php';
require_once '../../config/cors.php';


$database = new Database();
$db = $database->getConnection();

// ✅ Enable Error Reporting for Debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json'); // ✅ Ensure JSON response

// ✅ Ensure POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Method Not Allowed"]);
    exit();
}

// ✅ Debug Log
file_put_contents("debug_log.txt", print_r($_POST, true));
file_put_contents("debug_files.txt", print_r($_FILES, true));

// ✅ Get FormData Parameters
$equipment_id = $_POST['equipment_id'] ?? null;
$name = $_POST['name'] ?? null;
$status = $_POST['status'] ?? null;
$description = $_POST['description'] ?? null;
$location = $_POST['location'] ?? null;
$assigned_to = $_POST['assigned_to'] ?? null;
$warranty_expiration = $_POST['warranty_expiration'] ?? null;
$purchase_date = $_POST['purchase_date'] ?? null;
$imagePath = null;

if (!$equipment_id || !$name || !$status || !$description) {
    http_response_code(400);
    echo json_encode(["message" => "Missing required fields"]);
    exit();
}

if (!empty($_FILES['image']['name'])) {
    $targetDir = __DIR__ . "/../../uploads/"; 
    $fileName = basename($_FILES["image"]["name"]);
    $targetFilePath = $targetDir . $fileName;

    // ✅ Ensure Upload Directory Exists
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
        $imagePath = "uploads/" . $fileName; // ✅ Save the image path
    } else {
        error_log("Image upload failed for file: " . $_FILES["image"]["name"]);
        http_response_code(500);
        echo json_encode(["message" => "Image upload failed"]);
        exit();
    }
}

// ✅ Update Equipment Data
$query = "UPDATE Equipment SET 
            name = :name, 
            status = :status, 
            description = :description, 
            location = :location, 
            assigned_to = :assigned_to, 
            warranty_expiration = :warranty_expiration, 
            purchase_date = :purchase_date"
            . ($imagePath ? ", image_path = :image_path" : "") . " 
            WHERE equipment_id = :equipment_id";

$stmt = $db->prepare($query);
$stmt->bindParam(":name", $name);
$stmt->bindParam(":status", $status);
$stmt->bindParam(":description", $description);
$stmt->bindParam(":location", $location);
$stmt->bindParam(":assigned_to", $assigned_to);
$stmt->bindParam(":warranty_expiration", $warranty_expiration);
$stmt->bindParam(":purchase_date", $purchase_date);
$stmt->bindParam(":equipment_id", $equipment_id);

if ($imagePath) {
    $stmt->bindParam(":image_path", $imagePath);
}

if ($stmt->execute()) {
    echo json_encode(["message" => "Equipment updated successfully.", "image_path" => $imagePath]);
} else {
    error_log("Database update failed for equipment ID: " . $equipment_id);
    http_response_code(500);
    echo json_encode(["message" => "Failed to update equipment."]);
}
?>
