
<?php
// Include mail configuration
require_once '../config/mail_config.php';

/**
 * Compose and send a custom email
 * 
 * @param string $recipient Recipient email address
 * @param string $subject Email subject
 * @param string $content Email content
 * @param string $signature Email signature
 * @return array Status and any error message
 */
function composeAndSendEmail($recipient, $subject, $content, $signature = '') {
    // Validate recipient email
    if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        return array(
            'success' => false,
            'message' => 'Invalid recipient email'
        );
    }
    
    // Send the custom email using the function from mail_config.php
    return sendCustomEmail($recipient, $subject, $content, $signature);
}
?>
