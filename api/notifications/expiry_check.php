
<?php
// Script to check for expiring orders and send reminder emails
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../config/mail_config.php';

// Enable error logging for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php/expiry_error.log');

// Function to send expiry reminder to customer
function sendExpiryReminder($order, $daysLeft) {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Enable debugging
        $mail->SMTPDebug = PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
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
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('mail.enderhost@gmail.com', 'EnderHOST');
        $mail->addAddress($order['email'], $order['customer_name']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $daysLeft == 0 
            ? 'URGENT: Your Minecraft Server Has Expired - EnderHOST' 
            : 'REMINDER: Your Minecraft Server Will Expire Soon - EnderHOST';
        
        // HTML Email Body with dark background and light text
        $mailContent = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>EnderHOST - Server Expiry Notice</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    margin: 0;
                    padding: 0;
                    background-color: #222222;
                    color: #FFFFFF;
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
                    background-color: rgba(30, 30, 46, 0.8);
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
                .urgency {
                    font-size: 18px;
                    color: #ff6b6b;
                    font-weight: bold;
                    margin: 15px 0;
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
        <body>
            <div class="container">
                <div class="header">
                    <img src="https://www.enderhost.in/path-to-logo.png" alt="EnderHOST Logo" width="150">
                    <h1>' . ($daysLeft == 0 ? 'Your Minecraft Server Has Expired!' : 'Your Minecraft Server Will Expire Soon!') . '</h1>
                </div>
                <div class="content">
                    <p>Hello ' . $order['customer_name'] . ',</p>';
                    
        if ($daysLeft == 0) {
            $mailContent .= '
                    <p class="urgency">⚠️ Your Minecraft server has EXPIRED today! ⚠️</p>
                    <p>Your server is no longer active. To continue enjoying our services, you need to renew your subscription immediately.</p>';
        } else {
            $mailContent .= '
                    <p class="urgency">⚠️ Your Minecraft server will expire in ' . $daysLeft . ' days! ⚠️</p>
                    <p>To ensure uninterrupted service, please renew your subscription before it expires.</p>';
        }
        
        $mailContent .= '
                    <div class="server-details">
                        <h2>Server Details</h2>
                        <div class="detail-row">
                            <span class="label">Order ID:</span> ' . $order['order_id'] . '
                        </div>
                        <div class="detail-row">
                            <span class="label">Server Name:</span> ' . $order['server_name'] . '
                        </div>
                        <div class="detail-row">
                            <span class="label">Login Email:</span> ' . $order['email'] . '
                        </div>
                        <div class="detail-row">
                            <span class="label">Expiry Date:</span> ' . date('F j, Y', strtotime($order['expiry_date'])) . '
                        </div>
                    </div>
                    
                    <p>To access your server control panel, please visit our website and log in with the email and password provided.</p>
                    
                    <div style="text-align: center;">
                        <a href="https://panel.enderhost.in" class="button">Access Control Panel</a>
                    </div>
                    
                    <p>To renew your server, please join our Discord server and create a renewal ticket:</p>
                    <div style="text-align: center;">
                        <a href="https://discord.gg/bsGPB9VpUY" class="button">Join Discord & Renew</a>
                    </div>
                    
                    <p>Remember to have your Order ID and Server Name ready when creating a renewal ticket.</p>
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
        $mail->AltBody = "Hello {$order['customer_name']},\n\n".
                        ($daysLeft == 0 
                            ? "⚠️ Your Minecraft server has EXPIRED today! ⚠️\n\nYour server is no longer active. To continue enjoying our services, you need to renew your subscription immediately." 
                            : "⚠️ Your Minecraft server will expire in {$daysLeft} days! ⚠️\n\nTo ensure uninterrupted service, please renew your subscription before it expires.") . "\n\n".
                        "Server Details:\n".
                        "Order ID: {$order['order_id']}\n".
                        "Server Name: {$order['server_name']}\n".
                        "Login Email: {$order['email']}\n".
                        "Expiry Date: " . date('F j, Y', strtotime($order['expiry_date'])) . "\n\n".
                        "To access your control panel: https://panel.enderhost.in\n\n".
                        "To renew your server, please join our Discord server and create a renewal ticket:\n".
                        "https://discord.gg/bsGPB9VpUY\n\n".
                        "Thank you for choosing EnderHOST!\n".
                        "www.enderhost.in";
        
        $mail->send();
        
        // Log the success
        file_put_contents(__DIR__ . '/../reminder_email.log', date('Y-m-d H:i:s') . ": Reminder email sent successfully to {$order['email']} for order {$order['order_id']}\n", FILE_APPEND);
        
        return true;
    } catch (Exception $e) {
        // Log error information
        $errorMessage = "Reminder email could not be sent. Mailer Error: {$mail->ErrorInfo}\n";
        $errorMessage .= "Debug output: \n" . $debugOutput . "\n";
        file_put_contents(__DIR__ . '/../reminder_error.log', date('Y-m-d H:i:s') . ": " . $errorMessage . "\n\n", FILE_APPEND);
        return false;
    }
}

// Function to send admin notification
function sendAdminNotification($orders) {
    if (empty($orders)) {
        return false;
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Enable debugging
        $mail->SMTPDebug = PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
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
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('mail.enderhost@gmail.com', 'EnderHOST System');
        $mail->addAddress('mail.enderhost@gmail.com', 'EnderHOST Admin');
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Server Expiry Alert: ' . count($orders) . ' Servers Expiring Soon';
        
        // HTML Email Body
        $mailContent = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>EnderHOST - Admin Expiry Notice</title>
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
                    max-width: 800px;
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
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                    background-color: rgba(15, 23, 42, 0.6);
                    border-radius: 8px;
                    overflow: hidden;
                }
                th, td {
                    padding: 12px 15px;
                    text-align: left;
                    border-bottom: 1px solid rgba(138, 100, 255, 0.2);
                }
                th {
                    background-color: rgba(59, 130, 246, 0.2);
                    color: #8A64FF;
                    font-weight: bold;
                }
                tr:last-child td {
                    border-bottom: none;
                }
                .footer {
                    text-align: center;
                    padding-top: 20px;
                    border-top: 1px solid rgba(138, 100, 255, 0.3);
                    font-size: 12px;
                    color: #cccccc;
                }
                h1, h2 {
                    color: white;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Server Expiry Alert</h1>
                    <p>The following servers are expiring in 2 days</p>
                </div>
                <div class="content">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Server Name</th>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Expiry Date</th>
                            </tr>
                        </thead>
                        <tbody>';
        
        foreach ($orders as $order) {
            $mailContent .= '
                            <tr>
                                <td>' . $order['order_id'] . '</td>
                                <td>' . $order['server_name'] . '</td>
                                <td>' . $order['customer_name'] . '</td>
                                <td>' . $order['email'] . '</td>
                                <td>' . date('Y-m-d', strtotime($order['expiry_date'])) . '</td>
                            </tr>';
        }
        
        $mailContent .= '
                        </tbody>
                    </table>
                </div>
                <div class="footer">
                    <p>This is an automated system message from EnderHOST Server Management System.</p>
                    <p>&copy; ' . date('Y') . ' EnderHOST. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>';
        
        $mail->Body = $mailContent;
        
        // Plain text alternative
        $plainText = "Server Expiry Alert\n\n";
        $plainText .= "The following servers are expiring in 2 days:\n\n";
        
        foreach ($orders as $order) {
            $plainText .= "Order ID: {$order['order_id']}\n";
            $plainText .= "Server Name: {$order['server_name']}\n";
            $plainText .= "Customer: {$order['customer_name']}\n";
            $plainText .= "Email: {$order['email']}\n";
            $plainText .= "Expiry Date: " . date('Y-m-d', strtotime($order['expiry_date'])) . "\n\n";
        }
        
        $plainText .= "This is an automated system message from EnderHOST Server Management System.\n";
        
        $mail->AltBody = $plainText;
        
        $mail->send();
        
        // Log the success
        file_put_contents(__DIR__ . '/../admin_notification.log', date('Y-m-d H:i:s') . ": Admin notification sent successfully\n", FILE_APPEND);
        
        return true;
    } catch (Exception $e) {
        // Log error information
        $errorMessage = "Admin notification could not be sent. Mailer Error: {$mail->ErrorInfo}\n";
        $errorMessage .= "Debug output: \n" . $debugOutput . "\n";
        file_put_contents(__DIR__ . '/../admin_error.log', date('Y-m-d H:i:s') . ": " . $errorMessage . "\n\n", FILE_APPEND);
        return false;
    }
}

// ==========================================================
// Main execution starts here
// ==========================================================

$today = date('Y-m-d');
$two_days_from_now = date('Y-m-d', strtotime('+2 days'));
$admin_notification_orders = []; // Array to hold orders for admin notification

// 1. Check for servers expiring exactly today
$expiring_today_sql = "SELECT * FROM orders WHERE DATE(expiry_date) = ? AND status = 'active'";
$expiring_today_stmt = $conn->prepare($expiring_today_sql);
$expiring_today_stmt->bind_param("s", $today);
$expiring_today_stmt->execute();
$expiring_today_result = $expiring_today_stmt->get_result();

// Process servers expiring today
while ($order = $expiring_today_result->fetch_assoc()) {
    error_log("Processing expiration notification for order {$order['order_id']} (expiring today)");
    sendExpiryReminder($order, 0);
    
    // Update status to expired
    $update_sql = "UPDATE orders SET status = 'expired' WHERE order_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("s", $order['order_id']);
    $update_stmt->execute();
    $update_stmt->close();
}
$expiring_today_stmt->close();

// 2. Check for servers expiring in 2 days
$expiring_soon_sql = "SELECT * FROM orders WHERE DATE(expiry_date) = ? AND status = 'active'";
$expiring_soon_stmt = $conn->prepare($expiring_soon_sql);
$expiring_soon_stmt->bind_param("s", $two_days_from_now);
$expiring_soon_stmt->execute();
$expiring_soon_result = $expiring_soon_stmt->get_result();

// Process servers expiring in 2 days
while ($order = $expiring_soon_result->fetch_assoc()) {
    error_log("Processing expiration notification for order {$order['order_id']} (expiring in 2 days)");
    sendExpiryReminder($order, 2);
    
    // Add to admin notification list
    $admin_notification_orders[] = $order;
}
$expiring_soon_stmt->close();

// 3. Send admin notification for servers expiring in 2 days
if (!empty($admin_notification_orders)) {
    error_log("Sending admin notification for " . count($admin_notification_orders) . " soon-to-expire servers");
    sendAdminNotification($admin_notification_orders);
}

// Close connection
$conn->close();
error_log("Expiry check completed at " . date('Y-m-d H:i:s'));
echo "Expiry check completed at " . date('Y-m-d H:i:s');
?>
