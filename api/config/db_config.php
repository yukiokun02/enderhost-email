
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
    
    // Create users table if not exists
    $checkTableSql = "SHOW TABLES LIKE 'users'";
    $tableResult = mysqli_query($conn, $checkTableSql);
    
    if (mysqli_num_rows($tableResult) == 0) {
        // Create the users table with user_group field
        $createTableSql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            user_group ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if (!mysqli_query($conn, $createTableSql)) {
            throw new Exception("Error creating users table: " . mysqli_error($conn));
        }
        
        // Create default admin user with consistent hash
        $defaultUsername = 'admin';
        $defaultPassword = '$2y$10$x8H9Xb9aOQXJPh/zH2FKHuRcIi7/jOQk0l.ZNPZ5IMNjbhxIyhkhu'; // Hash for 'admin123'
        $defaultGroup = 'admin';
        
        $insertAdminSql = "INSERT INTO users (username, password, user_group) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insertAdminSql);
        mysqli_stmt_bind_param($stmt, "sss", $defaultUsername, $defaultPassword, $defaultGroup);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error creating default admin user: " . mysqli_error($conn));
        }
        
        // Log creation of users table
        $logsDir = __DIR__ . '/../logs';
        if (!file_exists($logsDir)) {
            mkdir($logsDir, 0755, true);
        }
        file_put_contents($logsDir . '/auth.log', date('Y-m-d H:i:s') . ": Created users table and default admin user\n", FILE_APPEND);
    }
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
