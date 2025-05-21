<?php
// DEBUG: log everything
error_log("=== SERVER VARS ===\n" . print_r($_SERVER, true));
if (function_exists('getallheaders')) {
    error_log("=== ALL HEADERS ===\n" . print_r(getallheaders(), true));
} else {
    error_log("=== APACHE REQUEST HEADERS ===\n" . print_r(apache_request_headers(), true));
}
$apiUrl = 'https://www.coinpayments.net/api.php';

// Get POST data
$postFields = file_get_contents('php://input');

// Or if you're using `http_build_query`, do this instead:
$postFields = $_POST;

// Get headers
$hmacHeader = $_SERVER['HTTP_HMAC'] ?? '';

// Check for HMAC
if (!$hmacHeader) {
    http_response_code(400);
    echo json_encode(['error' => 'No HMAC signature sent']);
    exit;
}

// Initialize cURL
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'HMAC: ' . $hmacHeader,
]);

$response = curl_exec($ch);
if ($response === false) {
    echo json_encode(['error' => 'cURL Error: ' . curl_error($ch)]);
    exit;
}

curl_close($ch);
header('Content-Type: application/json');
echo $response;
