<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once "../../config/database.php";
require '../../vendor/autoload.php'; // Include JWT library

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (empty($data['email']) || empty($data['password'])) {
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "Email and password are required."]);
    exit();
}

$email = $data['email'];
$password = $data['password'];

// Fetch user by email
$query = "SELECT employee_id, name, email, password_hash, role_id FROM Employees WHERE email = :email LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindParam(':email', $email);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    http_response_code(401); // Unauthorized
    echo json_encode(["message" => "Invalid email or password."]);
    exit();
}

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Verify password
if (!password_verify($password, $user['password_hash'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(["message" => "Invalid email or password."]);
    exit();
}

// Generate JWT token
$secret_key = "YOUR_SECRET_KEY";
$issuer_claim = "localhost"; // Issuer
$audience_claim = "localhost"; // Audience
$issued_at = time();
$expiration_time = $issued_at + (60 * 60); // Token valid for 1 hour
$payload = [
    "iss" => $issuer_claim,
    "aud" => $audience_claim,
    "iat" => $issued_at,
    "exp" => $expiration_time,
    "data" => [
        "employee_id" => $user['employee_id'],
        "name" => $user['name'],
        "email" => $user['email'],
        "role_id" => $user['role_id']
    ]
];

$jwt = JWT::encode($payload, $secret_key, 'HS256');

// Respond with token
http_response_code(200);
echo json_encode([
    "message" => "Login successful.",
    "token" => $jwt
]);
?>
