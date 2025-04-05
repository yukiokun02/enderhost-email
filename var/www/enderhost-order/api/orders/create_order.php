
<?php
// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and email configuration
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../config/mail_config.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"), true);

// Validate data
if (
    empty($data['server_name']) ||
    empty($data['email']) ||
    empty($data['password']) ||
    empty($data['customer_name'])
) {
    // Set response code - 400 bad request
    http_response_code(400);
    
    // Tell the user
    echo json_encode(array("message" => "Unable to create order. Data is incomplete."));
    exit();
}

// Generate a unique order ID (format: ORD-YYYYMMDD-XXXXX)
$order_id = 'ORD-' . date('Ymd') . '-' . substr(uniqid(), -5);

// Calculate expiry date (30 days from now)
$order_date = date('Y-m-d H:i:s');
$expiry_date = date('Y-m-d H:i:s', strtotime('+30 days'));

// Create order query
$query = "INSERT INTO orders
          (order_id, server_name, email, password, customer_name, order_date, expiry_date)
          VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($query);
$stmt->bind_param(
    "sssssss",
    $order_id,
    $data['server_name'],
    $data['email'],
    $data['password'],
    $data['customer_name'],
    $order_date,
    $expiry_date
);

// Execute the query
if ($stmt->execute()) {
    // Prepare order data for email
    $orderData = array(
        'order_id' => $order_id,
        'server_name' => $data['server_name'],
        'email' => $data['email'],
        'password' => $data['password'],
        'customer_name' => $data['customer_name'],
        'order_date' => $order_date,
        'expiry_date' => $expiry_date
    );
    
    // Send confirmation email
    $emailSent = sendOrderConfirmation($orderData);
    
    // Set response code - 201 created
    http_response_code(201);
    
    // Tell the user
    echo json_encode(array(
        "message" => "Order was created successfully.",
        "order_id" => $order_id,
        "email_sent" => $emailSent ? "yes" : "no"
    ));
} else {
    // Set response code - 503 service unavailable
    http_response_code(503);
    
    // Tell the user
    echo json_encode(array("message" => "Unable to create order. " . $stmt->error));
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
