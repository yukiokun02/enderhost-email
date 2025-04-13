<?php
// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

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
        // Enable more detailed debugging
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $debugOutput = '';
        $mail->Debugoutput = function($str, $level) use (&$debugOutput) {
            $debugOutput .= date('Y-m-d H:i:s') . ": " . $str . "\n";
        };

        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp-relay.brevo.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = '87821c001@smtp-brevo.com';
        $mail->Password   = 'G5yfcVOZT84BaAMI';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Set timeout values
        $mail->Timeout = 60; // seconds
        $mail->SMTPKeepAlive = true; // maintain the SMTP connection
        
        // Recipients
        $mail->setFrom('noreply@enderhost.in', 'EnderHOST');
        $mail->addAddress($orderData['email'], $orderData['customer_name']);
        $mail->addBCC('mail.enderhost@gmail.com'); // Send a copy to admin
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Minecraft Server is Ready - EnderHOST';
        
        // HTML Email Body with EnderHOST styling - STATIC COLORS with high contrast for details
        $mailContent = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="color-scheme" content="light only">
            <meta name="supported-color-schemes" content="light only">
            <title>EnderHOST - Your Server is Ready</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    margin: 0;
                    padding: 0;
                    background-color: #1A1F2C;
                    color: #FFFFFF;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #1A1F2C;
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
                    background-color: rgba(26, 31, 44, 0.8);
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
                    display: inline-block;
                    width: 150px;
                }
                .value {
                    color: #FFFFFF;
                    font-weight: 500;
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
                    font-weight: bold;
                }
                h1, h2 {
                    color: #FFFFFF;
                }
                a {
                    color: #3B82F6;
                }
                p {
                    color: #FFFFFF;
                }
            </style>
        </head>
        <body style="background-color: #1A1F2C; color: #FFFFFF;">
            <div class="container" style="background-color: #1A1F2C; border: 1px solid rgba(138, 100, 255, 0.3);">
                <div class="header">
                    <img src="https://www.enderhost.in/path-to-logo.png" alt="EnderHOST Logo" width="150">
                    <h1 style="color: #FFFFFF;">Your Minecraft Server is Ready!</h1>
                </div>
                <div class="content">
                    <p style="color: #FFFFFF;">Hello ' . $orderData['customer_name'] . ',</p>
                    <p style="color: #FFFFFF;">Thank you for choosing EnderHOST! Your Minecraft server has been successfully created and is now ready to use.</p>
                    
                    <div class="server-details" style="background-color: #141824; border: 1px solid #8A64FF;">
                        <h2 style="color: #FFFFFF; text-align: center; margin-bottom: 15px;">Server Details</h2>
                        
                        <div class="detail-row">
                            <span class="label" style="color: #8A64FF; font-weight: bold;">Order ID:</span> 
                            <span class="value" style="color: #FFFFFF;">' . $orderData['order_id'] . '</span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="label" style="color: #8A64FF; font-weight: bold;">Server Name:</span> 
                            <span class="value" style="color: #FFFFFF;">' . $orderData['server_name'] . '</span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="label" style="color: #8A64FF; font-weight: bold;">Login Email:</span> 
                            <span class="value" style="color: #FFFFFF;">' . $orderData['email'] . '</span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="label" style="color: #8A64FF; font-weight: bold;">Login Password:</span> 
                            <span class="value" style="color: #FFFFFF;">' . $orderData['password'] . '</span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="label" style="color: #8A64FF; font-weight: bold;">Order Date:</span> 
                            <span class="value" style="color: #FFFFFF;">' . date('F j, Y', strtotime($orderData['order_date'])) . '</span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="label" style="color: #8A64FF; font-weight: bold;">Expiry Date:</span> 
                            <span class="value" style="color: #FFFFFF;">' . date('F j, Y', strtotime($orderData['expiry_date'])) . '</span>
                        </div>
                    </div>
                    
                    <p style="color: #FFFFFF;">To access your server control panel, please visit our website and log in with the email and password provided above.</p>
                    
                    <div style="text-align: center;">
                        <a href="https://panel.enderhost.in" class="button" style="background: linear-gradient(to right, #8A64FF, #3B82F6); color: white;">Access Control Panel</a>
                    </div>
                    
                    <p style="color: #FFFFFF;">If you need any assistance or have questions, feel free to join our Discord community:</p>
                    
                    <div style="text-align: center;">
                        <a href="https://discord.gg/bsGPB9VpUY" class="button" style="background: linear-gradient(to right, #8A64FF, #3B82F6); color: white;">Join Our Discord</a>
                    </div>
                </div>
                <div class="footer">
                    <p style="color: #cccccc;">&copy; ' . date('Y') . ' EnderHOST. All rights reserved.</p>
                    <p><a href="https://www.enderhost.in" style="color: #3B82F6;">www.enderhost.in</a></p>
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
                         "https://panel.enderhost.in\n\n".
                         "If you need any assistance or have questions, feel free to join our Discord community:\n".
                         "https://discord.gg/bsGPB9VpUY\n\n".
                         "Thank you for choosing EnderHOST!\n".
                         "www.enderhost.in";
        
        // Attempt to send the email
        $mailSent = $mail->send();
        
        // Log the SMTP conversation regardless of success
        file_put_contents(__DIR__ . '/../mail_debug.log', date('Y-m-d H:i:s') . ": Mail to {$orderData['email']} " . 
                         ($mailSent ? "sent successfully" : "failed") . "\n" . $debugOutput . "\n\n", FILE_APPEND);
        
        return $mailSent;
    } catch (Exception $e) {
        // Log error information
        $errorMessage = "Order confirmation email could not be sent. Mailer Error: {$mail->ErrorInfo}\n";
        $errorMessage .= "Debug output: \n" . $debugOutput . "\n";
        file_put_contents(__DIR__ . '/../mail_error.log', date('Y-m-d H:i:s') . ": " . $errorMessage . "\n\n", FILE_APPEND);
        return false;
    }
}

/**
 * Test the email configuration
 * 
 * @param string $testEmail Email to send test to
 * @return array Status and any error message
 */
function testEmailConfiguration($testEmail = 'mail.enderhost@gmail.com') {
    $mail = new PHPMailer(true);
    
    try {
        // Enable debugging
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $debugOutput = '';
        $mail->Debugoutput = function($str, $level) use (&$debugOutput) {
            $debugOutput .= date('Y-m-d H:i:s') . ": " . $str . "\n";
        };

        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp-relay.brevo.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = '87821c001@smtp-brevo.com';
        $mail->Password   = 'G5yfcVOZT84BaAMI';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Set timeout values
        $mail->Timeout = 60;
        
        // Recipients
        $mail->setFrom('noreply@enderhost.in', 'EnderHOST Test');
        $mail->addAddress($testEmail);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'EnderHOST Email Test';
        $mail->Body    = 'This is a test email to verify the email configuration is working correctly.';
        $mail->AltBody = 'This is a test email to verify the email configuration is working correctly.';
        
        // Send the email and capture the result
        $result = $mail->send();
        
        // Log the test regardless of success
        file_put_contents(__DIR__ . '/../mail_test.log', date('Y-m-d H:i:s') . ": Test email to {$testEmail} " . 
                         ($result ? "sent successfully" : "failed") . "\n" . $debugOutput . "\n\n", FILE_APPEND);
        
        return array(
            'success' => true,
            'message' => 'Test email sent successfully'
        );
    } catch (Exception $e) {
        $errorMessage = "Test email could not be sent. Mailer Error: {$mail->ErrorInfo}\n";
        $errorMessage .= "Debug output: \n" . $debugOutput . "\n";
        
        // Log the error
        file_put_contents(__DIR__ . '/../mail_test_error.log', date('Y-m-d H:i:s') . ": " . $errorMessage . "\n\n", FILE_APPEND);
        
        return array(
            'success' => false,
            'message' => $mail->ErrorInfo
        );
    }
}

/**
 * Send a custom email with signature
 * 
 * @param string $recipient Recipient email address
 * @param string $subject Email subject
 * @param string $content Email content
 * @param string $signature Email signature (HTML)
 * @return array Status and any error message
 */
function sendCustomEmail($recipient, $subject, $content, $signature = '') {
    $mail = new PHPMailer(true);
    
    try {
        // Enable debugging
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $debugOutput = '';
        $mail->Debugoutput = function($str, $level) use (&$debugOutput) {
            $debugOutput .= date('Y-m-d H:i:s') . ": " . $str . "\n";
        };

        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp-relay.brevo.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = '87821c001@smtp-brevo.com';
        $mail->Password   = 'G5yfcVOZT84BaAMI';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Set timeout values
        $mail->Timeout = 60; // seconds
        $mail->SMTPKeepAlive = true; // maintain the SMTP connection
        
        // Recipients
        $mail->setFrom('noreply@enderhost.in', 'EnderHOST');
        $mail->addAddress($recipient);
        // Remove the BCC to prevent duplicate emails
        // $mail->addBCC('mail.enderhost@gmail.com'); 
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        
        // Add EnderHOST text at the top
        $emailContent = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                            <h1 style="text-align: center; color: #333;">EnderHOST</h1>
                            <div style="padding: 20px; background-color: #f9f9f9; border-radius: 5px;">
                                ' . $content . '
                            </div>';
        
        // Add signature if provided
        if (!empty($signature)) {
            $emailContent .= $signature;
        }
        
        $emailContent .= '<div style="margin-top: 20px; font-size: 12px; color: #777; text-align: center;">
                            <p>© ' . date('Y') . ' EnderHOST. All rights reserved.</p>
                          </div>
                        </div>';
        
        $mail->Body = $emailContent;
        $mail->AltBody = strip_tags(str_replace('<br>', "\n", $content));
        
        $mail->send();
        return array(
            'success' => true,
            'message' => 'Email sent successfully'
        );
    } catch (Exception $e) {
        return array(
            'success' => false,
            'message' => 'Email could not be sent. Error: ' . $mail->ErrorInfo
        );
    }
}
?>
