<?php
require_once '../../config/database.php';
require_once '../../config/cors.php';
require_once '../../config/auth.php'; // ✅ Ensure authentication is included

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

try {
    // ✅ Verify user authentication & role
    $user = verifyToken();
    if (!$user) {
        http_response_code(403);
        echo json_encode(["message" => "Unauthorized access."]);
        exit();
    }

    // ✅ Role-based access: Only Admins (1) & Managers (2) can create suppliers
    if ($user->role_id > 2) {
        http_response_code(403);
        echo json_encode(["message" => "Access denied. Only Admins or Managers can create suppliers."]);
        exit();
    }

    // ✅ Capture JSON input
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        throw new Exception("Invalid JSON input.");
    }

    // ✅ Extract and sanitize fields
    $name = isset($data['name']) ? trim($data['name']) : null;
    $contact_name = isset($data['contact_name']) ? trim($data['contact_name']) : null;
    $email = isset($data['email']) ? trim($data['email']) : null;
    $phone_number = isset($data['phone_number']) ? trim($data['phone_number']) : null;
    $address = isset($data['address']) ? trim($data['address']) : null;
    $city = isset($data['city']) ? trim($data['city']) : null;
    $country = isset($data['country']) ? trim($data['country']) : null;
    $image_url = isset($data['image_url']) ? trim($data['image_url']) : null;

    // ✅ Validate required fields
    $missing_fields = [];
    if (!$name) $missing_fields["name"] = "❌";
    if (!$contact_name) $missing_fields["contact_name"] = "❌";
    if (!$email) $missing_fields["email"] = "❌";
    if (!$phone_number) $missing_fields["phone_number"] = "❌";

    if (!empty($missing_fields)) {
        http_response_code(400);
        echo json_encode(["message" => "Missing required fields.", "missing_fields" => $missing_fields]);
        exit();
    }

    // ✅ Debug log (Optional: Remove in production)
    file_put_contents("debug_supplier_create.txt", print_r($data, true));

    // ✅ Insert Query
    $query = "INSERT INTO Suppliers (name, contact_name, email, phone_number, address, city, country, image_url) 
              VALUES (:name, :contact_name, :email, :phone_number, :address, :city, :country, :image_url)";

    $stmt = $db->prepare($query);
    $stmt->bindParam(":name", $name);
    $stmt->bindParam(":contact_name", $contact_name);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":phone_number", $phone_number);
    $stmt->bindParam(":address", $address);
    $stmt->bindParam(":city", $city);
    $stmt->bindParam(":country", $country);
    $stmt->bindParam(":image_url", $image_url);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(["message" => "Supplier created successfully."]);
    } else {
        throw new Exception("Database error. Could not insert supplier.");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Server Error: " . $e->getMessage()]);
}
?>
