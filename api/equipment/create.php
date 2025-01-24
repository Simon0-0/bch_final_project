<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";
include_once "../../config/auth.php";

$database = new Database();
$db = $database->getConnection();

// ✅ Ensure the request is a POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
    exit();
}

// ✅ Check if required fields are set
if (!isset($_POST['name'], $_POST['description'], $_POST['status'], $_POST['location'])) {
    http_response_code(400);
    echo json_encode(["message" => "Missing required fields"]);
    exit();
}

// ✅ Assign values to variables (Ensure proper handling of null values)
$name = $_POST['name'] ?? null;
$description = $_POST['description'] ?? null;
$status = $_POST['status'] ?? null;
$location = $_POST['location'] ?? null;
$assigned_to = !empty($_POST['assigned_to']) ? $_POST['assigned_to'] : null;
$supplier_id = !empty($_POST['supplier_id']) ? $_POST['supplier_id'] : null;
$purchase_date = !empty($_POST['purchase_date']) ? $_POST['purchase_date'] : null;
$warranty_expiration = !empty($_POST['warranty_expiration']) ? $_POST['warranty_expiration'] : null;
$created_at = date('Y-m-d H:i:s');
$updated_at = date('Y-m-d H:i:s');

$image_path = null; // Default to null

// ✅ Handle Image Upload
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    error_log("🟢 Received Image: " . print_r($_FILES['image'], true));
    
    $upload_dir = "../../uploads/";

    // ✅ Ensure the uploads directory exists
    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0777, true) && !is_dir($upload_dir)) {
        error_log("❌ Failed to create upload directory");
        http_response_code(500);
        echo json_encode(["message" => "Failed to create upload directory"]);
        exit();
    }

    // ✅ Validate file type for security
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));

    if (!in_array($file_extension, $allowed_extensions)) {
        error_log("❌ Invalid file type: " . $file_extension);
        http_response_code(400);
        echo json_encode(["message" => "Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed."]);
        exit();
    }

    // ✅ Generate a unique filename
    $file_name = uniqid("img_", true) . "." . $file_extension;
    $target_file = $upload_dir . $file_name;

    // ✅ Move uploaded file
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        error_log("✅ Image Successfully Uploaded: " . $target_file);
        $image_path = "uploads/" . $file_name; // Store relative path
    } else {
        error_log("❌ Failed to Upload Image");
        http_response_code(500);
        echo json_encode(["message" => "Failed to upload image"]);
        exit();
    }
} elseif (isset($_FILES['image']['error']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    error_log("❌ File upload error: " . $_FILES['image']['error']);
    http_response_code(500);
    echo json_encode(["message" => "File upload error"]);
    exit();
}

// ✅ Insert into Database
$query = "INSERT INTO Equipment (name, description, status, location, assigned_to, supplier_id, purchase_date, warranty_expiration, image_path, created_at, updated_at) 
          VALUES (:name, :description, :status, :location, :assigned_to, :supplier_id, :purchase_date, :warranty_expiration, :image_path, :created_at, :updated_at)";

$stmt = $db->prepare($query);
$stmt->bindParam(':name', $name, PDO::PARAM_STR);
$stmt->bindParam(':description', $description, PDO::PARAM_STR);
$stmt->bindParam(':status', $status, PDO::PARAM_STR);
$stmt->bindParam(':location', $location, PDO::PARAM_STR);
$stmt->bindParam(':assigned_to', $assigned_to, PDO::PARAM_INT);
$stmt->bindParam(':supplier_id', $supplier_id, PDO::PARAM_INT);
$stmt->bindParam(':purchase_date', $purchase_date);
$stmt->bindParam(':warranty_expiration', $warranty_expiration);
$stmt->bindParam(':image_path', $image_path, PDO::PARAM_STR);
$stmt->bindParam(':created_at', $created_at);
$stmt->bindParam(':updated_at', $updated_at);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode([
        "message" => "Equipment created successfully.",
        "image_url" => "http://localhost/bch_final_project/" . $image_path
    ]);
} else {
    http_response_code(500);
    error_log(print_r($stmt->errorInfo(), true));
    echo json_encode(["message" => "Failed to create equipment."]);
}
?>
