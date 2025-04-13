
<?php
// Include header with CORS and JSON response setup
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON data from the request
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Check if data is valid
if (!$data || !isset($data['recipient']) || !isset($data['subject']) || !isset($data['content'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

// Include mail configuration
require_once '../config/mail_config.php';

// Validate recipient email
if (!filter_var($data['recipient'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid recipient email']);
    exit;
}

// Get data from request
$recipient = $data['recipient'];
$subject = $data['subject'];
$content = $data['content'];
$signature = isset($data['signature']) ? $data['signature'] : '';

// Send the email
$result = sendCustomEmail($recipient, $subject, $content, $signature);

// Return the result
if ($result['success']) {
    echo json_encode(['success' => true, 'message' => 'Email sent successfully']);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => $result['message']]);
}
?>
