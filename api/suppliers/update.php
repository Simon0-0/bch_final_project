<?php
require_once '../../config/cors.php';
include_once "../../config/database.php";
include_once "../../config/auth.php";

$database = new Database();
$db = $database->getConnection();

$response = ["message" => "Invalid request."];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    // Get supplier ID (Required)
    $supplier_id = $_POST['supplier_id'] ?? null;
    if (empty($supplier_id)) {
        throw new Exception("Supplier ID is required.");
    }

    // Get other fields (Optional updates)
    $name = $_POST['name'] ?? null;
    $contact_name = $_POST['contact_name'] ?? null;
    $phone_number = $_POST['phone_number'] ?? null;
    $email = $_POST['email'] ?? null;
    $address = $_POST['address'] ?? null;
    $city = $_POST['city'] ?? null;
    $country = $_POST['country'] ?? null;
    $image_url = $_POST['image_url'] ?? null; // Use only image URLs

    // Construct SQL query dynamically
    $query = "UPDATE Suppliers SET ";
    $params = [];

    if (!empty($name)) {
        $query .= "name = :name, ";
        $params[':name'] = $name;
    }
    if (!empty($contact_name)) {
        $query .= "contact_name = :contact_name, ";
        $params[':contact_name'] = $contact_name;
    }
    if (!empty($phone_number)) {
        $query .= "phone_number = :phone_number, ";
        $params[':phone_number'] = $phone_number;
    }
    if (!empty($email)) {
        $query .= "email = :email, ";
        $params[':email'] = $email;
    }
    if (!empty($address)) {
        $query .= "address = :address, ";
        $params[':address'] = $address;
    }
    if (!empty($city)) {
        $query .= "city = :city, ";
        $params[':city'] = $city;
    }
    if (!empty($country)) {
        $query .= "country = :country, ";
        $params[':country'] = $country;
    }
    if (!empty($image_url)) {
        $query .= "image_url = :image_url, ";
        $params[':image_url'] = $image_url;
    }

    // Remove trailing comma
    $query = rtrim($query, ", ") . " WHERE supplier_id = :supplier_id";
    $params[':supplier_id'] = $supplier_id;

    // Prepare and execute statement
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    if ($stmt->execute()) {
        // Fetch updated supplier data
        $query = "SELECT image_url FROM Suppliers WHERE supplier_id = :supplier_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':supplier_id', $supplier_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode([
            "message" => "Supplier updated successfully.",
            "image_url" => $row['image_url'] ?? null  // Ensure image_url is returned
        ]);
    } else {
        throw new Exception("Failed to update supplier.");
    }
} catch (Exception $e) {
    http_response_code(400);
    $response["error"] = $e->getMessage();
}

echo json_encode($response);
?>
