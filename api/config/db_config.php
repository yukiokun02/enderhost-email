
<?php
// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'orderadmin');
define('DB_PASSWORD', 'CODENAMEorder@');
define('DB_NAME', 'orderdb');

// Error handling
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Establish database connection
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    // Check connection
    if ($conn === false) {
        throw new Exception("ERROR: Could not connect to database. " . mysqli_connect_error());
    }
    
    // Set character set
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    // Log the error
    file_put_contents(__DIR__ . '/../debug_db_error.log', date('Y-m-d H:i:s') . ": " . $e->getMessage() . PHP_EOL, FILE_APPEND);
    
    // If this is being accessed directly, show error
    if (php_sapi_name() !== 'cli') {
        http_response_code(500);
        echo json_encode(array("status" => "error", "message" => "Database connection failed: " . $e->getMessage()));
        exit();
    }
}
?>
