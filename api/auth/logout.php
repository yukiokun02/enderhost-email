
<?php
// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Start or resume session
session_start();

// Log the logout
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $logsDir = __DIR__ . '/../logs';
    if (!file_exists($logsDir)) {
        mkdir($logsDir, 0755, true);
    }
    file_put_contents($logsDir . '/auth.log', date('Y-m-d H:i:s') . ": User {$username} logged out\n", FILE_APPEND);
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

echo json_encode(['status' => 'success', 'message' => 'Logged out successfully']);
