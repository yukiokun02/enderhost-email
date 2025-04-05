
<?php
// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// For debugging, add error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database and email configuration
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../config/mail_config.php';

// Get posted data - use php://input for raw JSON
$rawData = file_get_contents("php://input");
file_put_contents(__DIR__ . '/debug_request.log', date('Y-m-d H:i:s') . ": " . $rawData . PHP_EOL, FILE_APPEND);

$data = json_decode($rawData, true);

// If JSON parsing failed, try to handle form data
if (json_last_error() !== JSON_ERROR_NONE) {
    $data = $_POST;
    file_put_contents(__DIR__ . '/debug_request.log', date('Y-m-d H:i:s') . ": Fallback to POST data: " . print_r($_POST, true) . PHP_EOL, FILE_APPEND);
}

// Validate data
if (
    empty($data['serverName']) ||
    empty($data['email']) ||
    empty($data['password']) ||
    empty($data['customerName'])
) {
    // Log the error
    file_put_contents(__DIR__ . '/debug_error.log', date('Y-m-d H:i:s') . ": Incomplete data: " . print_r($data, true) . PHP_EOL, FILE_APPEND);
    
    // Set response code - 400 bad request
    http_response_code(400);
    
    // Tell the user
    echo json_encode(array("status" => "error", "message" => "Unable to create order. Data is incomplete."));
    exit();
}

// Generate a unique order ID if not provided
if (empty($data['orderId'])) {
    $data['orderId'] = 'EH-' . date('Ymd') . '-' . substr(uniqid(), -5);
}

// Calculate expiry date (30 days from now)
$order_date = date('Y-m-d H:i:s');
$expiry_date = date('Y-m-d H:i:s', strtotime('+30 days'));

try {
    // Create order query
    $query = "INSERT INTO orders
              (order_id, server_name, email, password, customer_name, order_date, expiry_date)
              VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Database prepare error: " . $conn->error);
    }
    
    $stmt->bind_param(
        "sssssss",
        $data['orderId'],
        $data['serverName'],
        $data['email'],
        $data['password'],
        $data['customerName'],
        $order_date,
        $expiry_date
    );

    // Execute the query
    if ($stmt->execute()) {
        // Prepare order data for email
        $orderData = array(
            'order_id' => $data['orderId'],
            'server_name' => $data['serverName'],
            'email' => $data['email'],
            'password' => $data['password'],
            'customer_name' => $data['customerName'],
            'order_date' => $order_date,
            'expiry_date' => $expiry_date
        );
        
        // Send confirmation email
        $emailSent = sendOrderConfirmation($orderData);
        
        // Set response code - 201 created
        http_response_code(201);
        
        // Tell the user
        echo json_encode(array(
            "status" => "success",
            "message" => "Order was created successfully.",
            "order_id" => $data['orderId'],
            "email_sent" => $emailSent ? "yes" : "no"
        ));
    } else {
        throw new Exception("Database execute error: " . $stmt->error);
    }
} catch (Exception $e) {
    // Log the error
    file_put_contents(__DIR__ . '/debug_error.log', date('Y-m-d H:i:s') . ": Exception: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
    
    // Set response code - 500 server error
    http_response_code(500);
    
    // Tell the user
    echo json_encode(array("status" => "error", "message" => "Server error: " . $e->getMessage()));
}

// Close statement and connection if they exist
if (isset($stmt) && $stmt) {
    $stmt->close();
}
if (isset($conn) && $conn) {
    $conn->close();
}
?>
