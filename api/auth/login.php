
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
    // Check if users table exists and has the user_group column
    $checkTableSql = "SHOW TABLES LIKE 'users'";
    $tableResult = mysqli_query($conn, $checkTableSql);
    
    if (mysqli_num_rows($tableResult) == 0) {
        // Create the users table with user_group field
        $createTableSql = "CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            user_group ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if (!mysqli_query($conn, $createTableSql)) {
            throw new Exception("Error creating users table: " . mysqli_error($conn));
        }
        
        // Create default admin user
        $defaultUsername = 'admin';
        $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $defaultGroup = 'admin';
        
        $insertAdminSql = "INSERT INTO users (username, password, user_group) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insertAdminSql);
        mysqli_stmt_bind_param($stmt, "sss", $defaultUsername, $defaultPassword, $defaultGroup);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error creating default admin user: " . mysqli_error($conn));
        }
        
        // Log the creation of default admin
        file_put_contents(__DIR__ . '/../logs/auth.log', date('Y-m-d H:i:s') . ": Created default admin user with admin privileges\n", FILE_APPEND);
    } else {
        // Check if user_group column exists
        $checkColumnSql = "SHOW COLUMNS FROM users LIKE 'user_group'";
        $columnResult = mysqli_query($conn, $checkColumnSql);
        
        if (mysqli_num_rows($columnResult) == 0) {
            // Add user_group column if it doesn't exist
            $addColumnSql = "ALTER TABLE users ADD COLUMN user_group ENUM('admin', 'staff') NOT NULL DEFAULT 'staff'";
            if (!mysqli_query($conn, $addColumnSql)) {
                throw new Exception("Error adding user_group column: " . mysqli_error($conn));
            }
            
            // Update admin user to have admin role if it exists
            $updateAdminSql = "UPDATE users SET user_group = 'admin' WHERE username = 'admin'";
            mysqli_query($conn, $updateAdminSql);
        }
        
        // Check if admin user exists, if not create it
        $checkAdminSql = "SELECT id FROM users WHERE username = 'admin'";
        $adminResult = mysqli_query($conn, $checkAdminSql);
        
        if (mysqli_num_rows($adminResult) == 0) {
            // Create default admin user
            $defaultUsername = 'admin';
            $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $defaultGroup = 'admin';
            
            $insertAdminSql = "INSERT INTO users (username, password, user_group) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insertAdminSql);
            mysqli_stmt_bind_param($stmt, "sss", $defaultUsername, $defaultPassword, $defaultGroup);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error creating default admin user: " . mysqli_error($conn));
            }
            
            // Log the creation of default admin
            file_put_contents(__DIR__ . '/../logs/auth.log', date('Y-m-d H:i:s') . ": Created default admin user with admin privileges\n", FILE_APPEND);
        }
    }
    
    // Check user credentials
    $sql = "SELECT id, username, password, user_group FROM users WHERE username = ?";
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
            $_SESSION['user_group'] = $user['user_group'];
            $_SESSION['last_activity'] = time();
            
            // Log successful login
            file_put_contents(__DIR__ . '/../logs/auth.log', date('Y-m-d H:i:s') . ": Login successful for user {$user['username']} (group: {$user['user_group']})\n", FILE_APPEND);
            
            echo json_encode([
                'status' => 'success', 
                'message' => 'Login successful',
                'username' => $user['username'],
                'userGroup' => $user['user_group']
            ]);
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
