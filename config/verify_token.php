<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function verifyToken() {
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        http_response_code(401); // Unauthorized
        echo json_encode(["message" => "Access token is missing."]);
        exit();
    }

    $authHeader = $headers['Authorization'];
    $arr = explode(" ", $authHeader);

    if (count($arr) != 2 || $arr[0] !== "Bearer") {
        http_response_code(401); // Unauthorized
        echo json_encode(["message" => "Invalid token format."]);
        exit();
    }

    $jwt = $arr[1];
    $secret_key = "SimonaKocisova";

    try {
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
        return $decoded->data; // Return user data from token payload
    } catch (Exception $e) {
        http_response_code(401); // Unauthorized
        echo json_encode(["message" => "Access token is invalid."]);
        exit();
    }
}
?>
