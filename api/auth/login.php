
<?php
// Enable error reporting for development
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// Set headers
header('Content-Type: application/json');
session_start();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Include database configuration
require_once '../config/db_config.php';

// Check if the input is valid
if (!isset($input['username']) || !isset($input['password'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Username and password are required']);
    exit;
}

$username = mysqli_real_escape_string($conn, $input['username']);
$password = $input['password'];

try {
    // Create users table if it doesn't exist (first time setup)
    $checkTableSql = "SHOW TABLES LIKE 'users'";
    $tableResult = mysqli_query($conn, $checkTableSql);
    
    if (mysqli_num_rows($tableResult) == 0) {
        // Create the users table
        $createTableSql = "CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if (!mysqli_query($conn, $createTableSql)) {
            throw new Exception("Error creating users table: " . mysqli_error($conn));
        }
        
        // Create default admin user (will be used for first login)
        $defaultUsername = 'admin';
        $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
        
        $insertAdminSql = "INSERT INTO users (username, password) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $insertAdminSql);
        mysqli_stmt_bind_param($stmt, "ss", $defaultUsername, $defaultPassword);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error creating default admin user: " . mysqli_error($conn));
        }
        
        // Log the creation of default admin
        file_put_contents(__DIR__ . '/../logs/auth.log', date('Y-m-d H:i:s') . ": Created default admin user\n", FILE_APPEND);
    }
    
    // Check user credentials
    $sql = "SELECT id, username, password FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Login query failed: " . mysqli_error($conn));
    }
    
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password'])) {
            // Password is correct, set up session
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['last_activity'] = time();
            
            // Log successful login
            file_put_contents(__DIR__ . '/../logs/auth.log', date('Y-m-d H:i:s') . ": Login successful for user {$user['username']}\n", FILE_APPEND);
            
            echo json_encode(['status' => 'success', 'message' => 'Login successful']);
        } else {
            // Log failed login attempt
            file_put_contents(__DIR__ . '/../logs/auth.log', date('Y-m-d H:i:s') . ": Failed login attempt for user {$username} (incorrect password)\n", FILE_APPEND);
            
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid username or password']);
        }
    } else {
        // Log failed login attempt
        file_put_contents(__DIR__ . '/../logs/auth.log', date('Y-m-d H:i:s') . ": Failed login attempt for non-existent user {$username}\n", FILE_APPEND);
        
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid username or password']);
    }
} catch (Exception $e) {
    // Log error
    file_put_contents(__DIR__ . '/../logs/auth.log', date('Y-m-d H:i:s') . ": Login error: " . $e->getMessage() . "\n", FILE_APPEND);
    
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'An unexpected error occurred']);
}

// Close connection
mysqli_close($conn);
