
<?php
// Set headers
header('Content-Type: application/json');
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Include database configuration
require_once '../config/db_config.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

try {
    // List users
    if ($method === 'GET') {
        $sql = "SELECT id, username, created_at FROM users ORDER BY username";
        $result = mysqli_query($conn, $sql);
        
        if (!$result) {
            throw new Exception("Error fetching users: " . mysqli_error($conn));
        }
        
        $users = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'users' => $users]);
    }
    
    // Create new user
    elseif ($method === 'POST' && isset($input['action']) && $input['action'] === 'create') {
        if (!isset($input['username']) || !isset($input['password'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Username and password are required']);
            exit;
        }
        
        $username = mysqli_real_escape_string($conn, $input['username']);
        $password = password_hash($input['password'], PASSWORD_DEFAULT);
        
        // Check if username already exists
        $checkSql = "SELECT id FROM users WHERE username = ?";
        $checkStmt = mysqli_prepare($conn, $checkSql);
        mysqli_stmt_bind_param($checkStmt, "s", $username);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);
        
        if (mysqli_stmt_num_rows($checkStmt) > 0) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'Username already exists']);
            exit;
        }
        
        // Create new user
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $username, $password);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error creating user: " . mysqli_error($conn));
        }
        
        // Log user creation
        file_put_contents(__DIR__ . '/../logs/auth.log', date('Y-m-d H:i:s') . ": User {$_SESSION['username']} created new user {$username}\n", FILE_APPEND);
        
        echo json_encode(['status' => 'success', 'message' => 'User created successfully']);
    }
    
    // Delete user
    elseif ($method === 'POST' && isset($input['action']) && $input['action'] === 'delete') {
        if (!isset($input['user_id'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
            exit;
        }
        
        $user_id = (int)$input['user_id'];
        
        // Don't allow deleting the current user
        if ($user_id === (int)$_SESSION['user_id']) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete your own account']);
            exit;
        }
        
        // Get username before deleting for logging
        $getUsernameSql = "SELECT username FROM users WHERE id = ?";
        $getUsernameStmt = mysqli_prepare($conn, $getUsernameSql);
        mysqli_stmt_bind_param($getUsernameStmt, "i", $user_id);
        mysqli_stmt_execute($getUsernameStmt);
        $usernameResult = mysqli_stmt_get_result($getUsernameStmt);
        $usernameRow = mysqli_fetch_assoc($usernameResult);
        $deletedUsername = $usernameRow ? $usernameRow['username'] : 'unknown';
        
        // Delete user
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error deleting user: " . mysqli_error($conn));
        }
        
        if (mysqli_affected_rows($conn) === 0) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
            exit;
        }
        
        // Log user deletion
        file_put_contents(__DIR__ . '/../logs/auth.log', date('Y-m-d H:i:s') . ": User {$_SESSION['username']} deleted user {$deletedUsername}\n", FILE_APPEND);
        
        echo json_encode(['status' => 'success', 'message' => 'User deleted successfully']);
    }
    
    // Change password
    elseif ($method === 'POST' && isset($input['action']) && $input['action'] === 'change_password') {
        if (!isset($input['user_id']) || !isset($input['new_password'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'User ID and new password are required']);
            exit;
        }
        
        $user_id = (int)$input['user_id'];
        $new_password = password_hash($input['new_password'], PASSWORD_DEFAULT);
        
        // Get username for logging
        $getUsernameSql = "SELECT username FROM users WHERE id = ?";
        $getUsernameStmt = mysqli_prepare($conn, $getUsernameSql);
        mysqli_stmt_bind_param($getUsernameStmt, "i", $user_id);
        mysqli_stmt_execute($getUsernameStmt);
        $usernameResult = mysqli_stmt_get_result($getUsernameStmt);
        $usernameRow = mysqli_fetch_assoc($usernameResult);
        
        if (!$usernameRow) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
            exit;
        }
        
        $targetUsername = $usernameRow['username'];
        
        // Update password
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $new_password, $user_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error changing password: " . mysqli_error($conn));
        }
        
        // Log password change
        file_put_contents(__DIR__ . '/../logs/auth.log', date('Y-m-d H:i:s') . ": User {$_SESSION['username']} changed password for user {$targetUsername}\n", FILE_APPEND);
        
        echo json_encode(['status' => 'success', 'message' => 'Password changed successfully']);
    }
    
    // Invalid action
    else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    // Log error
    file_put_contents(__DIR__ . '/../logs/auth.log', date('Y-m-d H:i:s') . ": User management error: " . $e->getMessage() . "\n", FILE_APPEND);
    
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'An unexpected error occurred']);
}

// Close connection
mysqli_close($conn);
