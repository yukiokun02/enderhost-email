
<?php
// Set proper headers for an API response
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Include the mail configuration
require_once __DIR__ . '/config/mail_config.php';

// Optionally get a test email from the request
$testEmail = isset($_GET['email']) ? $_GET['email'] : 'mail.enderhost@gmail.com';

// Attempt to send a test email
$result = testEmailConfiguration($testEmail);

// Return the result
echo json_encode($result);
?>
