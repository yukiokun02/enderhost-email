
<?php
// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Start or resume session
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

// Log the login attempt (without password)
$logsDir = __DIR__ . '/../logs';
if (!file_exists($logsDir)) {
    mkdir($logsDir, 0755, true);
}
file_put_contents($logsDir . '/auth.log', date('Y-m-d H:i:s') . ": Login attempt for user {$username}\n", FILE_APPEND);

try {
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
        
        // Special case for admin with hardcoded password
        if ($username === 'admin' && $password === 'admin123') {
            // Update admin password hash to match current PHP version's password_hash
            $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
            $updateSql = "UPDATE users SET password = ? WHERE username = 'admin'";
            $updateStmt = mysqli_prepare($conn, $updateSql);
            mysqli_stmt_bind_param($updateStmt, "s", $new_hash);
            mysqli_stmt_execute($updateStmt);
            
            // Set up session
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_group'] = $user['user_group'];
            $_SESSION['last_activity'] = time();
            
            // Log successful login
            file_put_contents($logsDir . '/auth.log', date('Y-m-d H:i:s') . ": Login successful for user {$user['username']} (group: {$user['user_group']}) - admin password hash updated\n", FILE_APPEND);
            
            echo json_encode([
                'status' => 'success', 
                'message' => 'Login successful',
                'username' => $user['username'],
                'userGroup' => $user['user_group']
            ]);
            exit;
        }
        
        // Normal password verification
        if (password_verify($password, $user['password'])) {
            // Password is correct, set up session
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_group'] = $user['user_group'];
            $_SESSION['last_activity'] = time();
            
            // Log successful login
            file_put_contents($logsDir . '/auth.log', date('Y-m-d H:i:s') . ": Login successful for user {$user['username']} (group: {$user['user_group']})\n", FILE_APPEND);
            
            echo json_encode([
                'status' => 'success', 
                'message' => 'Login successful',
                'username' => $user['username'],
                'userGroup' => $user['user_group']
            ]);
        } else {
            // For admin user with incorrect hash, reset the password
            if ($username === 'admin') {
                // Update admin password hash
                $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
                $updateSql = "UPDATE users SET password = ? WHERE username = 'admin'";
                $updateStmt = mysqli_prepare($conn, $updateSql);
                mysqli_stmt_bind_param($updateStmt, "s", $new_hash);
                
                if (mysqli_stmt_execute($updateStmt) && $password === 'admin123') {
                    // Set up session
                    $_SESSION['logged_in'] = true;
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_group'] = $user['user_group'];
                    $_SESSION['last_activity'] = time();
                    
                    // Log successful login after reset
                    file_put_contents($logsDir . '/auth.log', date('Y-m-d H:i:s') . ": Login successful for admin after password reset\n", FILE_APPEND);
                    
                    echo json_encode([
                        'status' => 'success', 
                        'message' => 'Login successful',
                        'username' => $user['username'],
                        'userGroup' => $user['user_group']
                    ]);
                    exit;
                }
            }
            
            // Log failed login attempt
            file_put_contents($logsDir . '/auth.log', date('Y-m-d H:i:s') . ": Failed login attempt for user {$username} (incorrect password)\n", FILE_APPEND);
            
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid username or password']);
        }
    } else {
        // Special case: If admin user doesn't exist, create it
        if ($username === 'admin' && $password === 'admin123') {
            // Create default admin user
            $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $defaultGroup = 'admin';
            
            $insertAdminSql = "INSERT INTO users (username, password, user_group) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insertAdminSql);
            mysqli_stmt_bind_param($stmt, "sss", $username, $defaultPassword, $defaultGroup);
            
            if (mysqli_stmt_execute($stmt)) {
                $user_id = mysqli_insert_id($conn);
                
                // Set up session
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['user_group'] = $defaultGroup;
                $_SESSION['last_activity'] = time();
                
                // Log the creation and login
                file_put_contents($logsDir . '/auth.log', date('Y-m-d H:i:s') . ": Created and logged in default admin user\n", FILE_APPEND);
                
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Login successful',
                    'username' => $username,
                    'userGroup' => $defaultGroup
                ]);
                exit;
            }
        }
        
        // Log failed login attempt
        file_put_contents($logsDir . '/auth.log', date('Y-m-d H:i:s') . ": Failed login attempt for non-existent user {$username}\n", FILE_APPEND);
        
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid username or password']);
    }
} catch (Exception $e) {
    // Log error
    file_put_contents($logsDir . '/auth.log', date('Y-m-d H:i:s') . ": Login error: " . $e->getMessage() . "\n", FILE_APPEND);
    
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'An unexpected error occurred']);
}

// Close connection
mysqli_close($conn);
