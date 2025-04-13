
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

// Include mail composer functionality
require_once 'mail_composer.php';

// Get data from request
$recipient = $data['recipient'];
$subject = $data['subject'];
$content = $data['content'];
$signature = isset($data['signature']) ? $data['signature'] : '';

// Compose and send the email
$result = composeAndSendEmail($recipient, $subject, $content, $signature);

// Return the result
if ($result['success']) {
    echo json_encode(['success' => true, 'message' => 'Email sent successfully']);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => $result['message']]);
}
?>
