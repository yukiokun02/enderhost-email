
<?php
// Set headers
header('Content-Type: application/json');

// Start or resume session
session_start();

// Check if the user is logged in and session is valid
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Check for session expiration (30 minutes inactivity)
    $max_idle_time = 30 * 60; // 30 minutes in seconds
    
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $max_idle_time)) {
        // Session has expired
        $_SESSION = array();
        session_destroy();
        
        echo json_encode([
            'status' => 'error',
            'authenticated' => false,
            'message' => 'Session expired'
        ]);
    } else {
        // Update last activity time
        $_SESSION['last_activity'] = time();
        
        echo json_encode([
            'status' => 'success',
            'authenticated' => true,
            'username' => $_SESSION['username'],
            'userGroup' => $_SESSION['user_group'] ?? 'staff'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'authenticated' => false,
        'message' => 'Not authenticated'
    ]);
}
