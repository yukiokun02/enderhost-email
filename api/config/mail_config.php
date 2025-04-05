<?php
// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Send order confirmation email to customer
 * 
 * @param array $orderData Order information
 * @return bool Success status
 */
function sendOrderConfirmation($orderData) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp-relay.brevo.com'; // Your Brevo SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your_brevo_username'; // Your Brevo username
        $mail->Password   = 'your_brevo_password'; // Your Brevo password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('noreply@enderhost.in', 'EnderHOST');
        $mail->addAddress($orderData['email'], $orderData['customer_name']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Minecraft Server is Ready - EnderHOST';
        
        // HTML Email Body with EnderHOST styling
        $mailContent = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>EnderHOST - Your Server is Ready</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    margin: 0;
                    padding: 0;
                    background-color: #0F172A;
                    color: #ffffff;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #1E1E2E;
                    border-radius: 8px;
                    border: 1px solid rgba(138, 100, 255, 0.3);
                }
                .header {
                    text-align: center;
                    padding-bottom: 20px;
                    border-bottom: 1px solid rgba(138, 100, 255, 0.3);
                }
                .content {
                    padding: 20px 0;
                }
                .server-details {
                    background-color: rgba(15, 23, 42, 0.6);
                    border-radius: 8px;
                    padding: 15px;
                    margin: 20px 0;
                }
                .detail-row {
                    padding: 8px 0;
                    border-bottom: 1px solid rgba(138, 100, 255, 0.2);
                }
                .detail-row:last-child {
                    border-bottom: none;
                }
                .label {
                    font-weight: bold;
                    color: #8A64FF;
                }
                .footer {
                    text-align: center;
                    padding-top: 20px;
                    border-top: 1px solid rgba(138, 100, 255, 0.3);
                    font-size: 12px;
                    color: #cccccc;
                }
                .button {
                    display: inline-block;
                    background: linear-gradient(to right, #8A64FF, #3B82F6);
                    color: white;
                    padding: 10px 20px;
                    text-decoration: none;
                    border-radius: 5px;
                    margin-top: 15px;
                }
                h1, h2 {
                    color: white;
                }
                a {
                    color: #3B82F6;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <img src="https://www.enderhost.in/path-to-logo.png" alt="EnderHOST Logo" width="150">
                    <h1>Your Minecraft Server is Ready!</h1>
                </div>
                <div class="content">
                    <p>Hello ' . $orderData['customer_name'] . ',</p>
                    <p>Thank you for choosing EnderHOST! Your Minecraft server has been successfully created and is now ready to use.</p>
                    
                    <div class="server-details">
                        <h2>Server Details</h2>
                        <div class="detail-row">
                            <span class="label">Order ID:</span> ' . $orderData['order_id'] . '
                        </div>
                        <div class="detail-row">
                            <span class="label">Server Name:</span> ' . $orderData['server_name'] . '
                        </div>
                        <div class="detail-row">
                            <span class="label">Login Email:</span> ' . $orderData['email'] . '
                        </div>
                        <div class="detail-row">
                            <span class="label">Login Password:</span> ' . $orderData['password'] . '
                        </div>
                        <div class="detail-row">
                            <span class="label">Order Date:</span> ' . date('F j, Y', strtotime($orderData['order_date'])) . '
                        </div>
                        <div class="detail-row">
                            <span class="label">Expiry Date:</span> ' . date('F j, Y', strtotime($orderData['expiry_date'])) . '
                        </div>
                    </div>
                    
                    <p>To access your server control panel, please visit our website and log in with the email and password provided above.</p>
                    
                    <div style="text-align: center;">
                        <a href="https://www.enderhost.in/login" class="button">Access Control Panel</a>
                    </div>
                    
                    <p>If you need any assistance or have questions, feel free to join our Discord community:</p>
                    
                    <div style="text-align: center;">
                        <a href="https://discord.gg/bsGPB9VpUY" class="button">Join Our Discord</a>
                    </div>
                </div>
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' EnderHOST. All rights reserved.</p>
                    <p><a href="https://www.enderhost.in">www.enderhost.in</a></p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        $mail->Body = $mailContent;
        $mail->AltBody = "Hello {$orderData['customer_name']},\n\n".
                         "Thank you for choosing EnderHOST! Your Minecraft server has been successfully created and is now ready to use.\n\n".
                         "Server Details:\n".
                         "Order ID: {$orderData['order_id']}\n".
                         "Server Name: {$orderData['server_name']}\n".
                         "Login Email: {$orderData['email']}\n".
                         "Login Password: {$orderData['password']}\n".
                         "Order Date: " . date('F j, Y', strtotime($orderData['order_date'])) . "\n".
                         "Expiry Date: " . date('F j, Y', strtotime($orderData['expiry_date'])) . "\n\n".
                         "To access your server control panel, please visit our website and log in with the email and password provided above.\n".
                         "https://www.enderhost.in/login\n\n".
                         "If you need any assistance or have questions, feel free to join our Discord community:\n".
                         "https://discord.gg/bsGPB9VpUY\n\n".
                         "Thank you for choosing EnderHOST!\n".
                         "www.enderhost.in";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Order confirmation email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
