
# EnderHOST Order System

## Overview
This is a web application for EnderHOST that allows collection of Minecraft server orders. The system captures customer details, stores them in a MariaDB database, and sends confirmation emails with login credentials to customers. It also includes an automated system for sending expiration reminders to customers.

## System Requirements
- Linux VPS with root access
- Nginx web server
- PHP 7.4+ with php-fpm
- MariaDB/MySQL
- PHPMailer for email delivery
- Brevo SMTP account (already set up)
- Cron job capability for scheduled tasks

## Project Structure
```
enderhost-order/
├── public/              # Public web files
│   ├── index.html       # Main HTML file
│   ├── assets/          # Compiled JS/CSS
│   └── images/          # Images and logo
├── api/                 # Backend PHP scripts
│   ├── order.php        # Order processing script
│   ├── expiry_check.php # Expiration reminder script
│   ├── db_config.php    # Database configuration
│   └── mail_config.php  # Email configuration
└── database/            # Database scripts
    └── setup.sql        # Database setup script
```

## Installation Steps

### 1. Update System Packages
```bash
sudo apt update
sudo apt upgrade -y
```

### 2. Install Required Software (if not already installed)
```bash
sudo apt install -y nginx mariadb-server php-fpm php-mysql php-curl php-mbstring php-json php-xml git unzip
```

### 3. Clone Repository (or upload files to server)
```bash
# If using Git
mkdir -p /var/www/enderhost-order
git clone [your-repo-url] /var/www/enderhost-order

# Alternatively, upload files via SFTP to /var/www/enderhost-order
```

### 4. Set Up MariaDB Database
```bash
sudo mysql -u root
```

Run the following SQL commands:
```sql
CREATE DATABASE enderhost_orders;
CREATE USER 'enderhost_user'@'localhost' IDENTIFIED BY 'choose_a_strong_password';
GRANT ALL PRIVILEGES ON enderhost_orders.* TO 'enderhost_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 5. Create Database Tables
```bash
sudo mysql -u root enderhost_orders < /var/www/enderhost-order/database/setup.sql
```

Create the `database/setup.sql` file with:
```bash
cat > /var/www/enderhost-order/database/setup.sql << 'EOF'
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(50) NOT NULL UNIQUE,
    server_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password VARCHAR(100) NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expiry_date DATETIME NOT NULL,
    status VARCHAR(20) DEFAULT 'active'
);
EOF
```

### 6. Set Up Backend PHP Files

Create the database configuration file:
```bash
mkdir -p /var/www/enderhost-order/api
cat > /var/www/enderhost-order/api/db_config.php << 'EOF'
<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'enderhost_user');
define('DB_PASSWORD', 'choose_a_strong_password'); // Use the password you set earlier
define('DB_NAME', 'enderhost_orders');

// Establish database connection
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn === false) {
    die("ERROR: Could not connect to database. " . mysqli_connect_error());
}
?>
EOF
```

Create the mail configuration file:
```bash
cat > /var/www/enderhost-order/api/mail_config.php << 'EOF'
<?php
// PHPMailer Import
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendOrderConfirmation($order) {
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
        $mail->addAddress($order['email'], $order['customer_name']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Minecraft Server is Ready! - EnderHOST';
        
        // HTML Email Body
        $mailContent = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>EnderHOST - Server Confirmation</title>
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
                    <p>Hello ' . $order['customer_name'] . ',</p>
                    <p>Thank you for choosing EnderHOST! Your Minecraft server has been successfully created and is now ready to use.</p>
                    
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
                            <span class="label">Password:</span> ' . $order['password'] . '
                        </div>
                        <div class="detail-row">
                            <span class="label">Activation Date:</span> ' . date('F j, Y', strtotime($order['order_date'])) . '
                        </div>
                        <div class="detail-row">
                            <span class="label">Expiry Date:</span> ' . date('F j, Y', strtotime($order['expiry_date'])) . '
                        </div>
                    </div>
                    
                    <p>Please save these details for future reference. You\'ll need them when renewing your server.</p>
                    
                    <p>To access your server control panel, click the button below:</p>
                    <div style="text-align: center;">
                        <a href="https://panel.enderhost.in" class="button">Access Control Panel</a>
                    </div>
                    
                    <p>If you have any questions or need assistance, please don\'t hesitate to contact our support team.</p>
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
                         "Your Minecraft server has been created.\n".
                         "Order ID: {$order['order_id']}\n".
                         "Server Name: {$order['server_name']}\n".
                         "Login Email: {$order['email']}\n".
                         "Password: {$order['password']}\n".
                         "Activation Date: " . date('F j, Y', strtotime($order['order_date'])) . "\n".
                         "Expiry Date: " . date('F j, Y', strtotime($order['expiry_date'])) . "\n\n".
                         "Access your control panel at: https://panel.enderhost.in\n\n".
                         "Thank you for choosing EnderHOST!\n".
                         "www.enderhost.in";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
EOF
```

Create the order processing file:
```bash
cat > /var/www/enderhost-order/api/order.php << 'EOF'
<?php
header('Content-Type: application/json');

// Enable error reporting for debugging (disable in production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Include database and mail configuration
require_once 'db_config.php';
require_once 'mail_config.php';

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if this is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get the JSON data from the request
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);
    
    // Check if data is valid JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data']);
        exit;
    }
    
    // Validate required fields
    $required_fields = ['orderId', 'serverName', 'email', 'password', 'customerName'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required field: ' . $field]);
            exit;
        }
    }
    
    // Sanitize input data
    $order_id = sanitize_input($data['orderId']);
    $server_name = sanitize_input($data['serverName']);
    $email = sanitize_input($data['email']);
    $password = sanitize_input($data['password']);
    $customer_name = sanitize_input($data['customerName']);
    
    // Calculate expiry date (30 days from now)
    $order_date = date('Y-m-d H:i:s');
    $expiry_date = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    // Check if order_id already exists
    $check_sql = "SELECT order_id FROM orders WHERE order_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $order_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Order ID already exists']);
        exit;
    }
    
    // Prepare SQL statement to prevent SQL injection
    $sql = "INSERT INTO orders (order_id, server_name, email, password, customer_name, order_date, expiry_date) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $order_id, $server_name, $email, $password, $customer_name, $order_date, $expiry_date);
    
    // Execute the statement
    if ($stmt->execute()) {
        // Prepare order data for email
        $order = [
            'order_id' => $order_id,
            'server_name' => $server_name,
            'email' => $email,
            'password' => $password,
            'customer_name' => $customer_name,
            'order_date' => $order_date,
            'expiry_date' => $expiry_date
        ];
        
        // Send confirmation email
        if (sendOrderConfirmation($order)) {
            echo json_encode(['status' => 'success', 'message' => 'Order processed successfully and email sent']);
        } else {
            echo json_encode(['status' => 'warning', 'message' => 'Order saved but email could not be sent']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }
    
    // Close statement and connection
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Only POST requests are allowed']);
}
?>
EOF
```

Create the expiry check script:
```bash
cat > /var/www/enderhost-order/api/expiry_check.php << 'EOF'
<?php
// Script content will be copied from the implementation above
// This is a placeholder - use the full script content from earlier
EOF
```

### 7. Install PHPMailer via Composer

```bash
cd /var/www/enderhost-order/api
curl -sS https://getcomposer.org/installer | php
php composer.phar require phpmailer/phpmailer
```

### 8. Configure Nginx

Create a new Nginx site configuration:
```bash
sudo nano /etc/nginx/sites-available/enderhost-order
```

Add the following configuration:
```nginx
server {
    listen 80;
    server_name order.enderhost.in; # Replace with your domain
    root /var/www/enderhost-order/public;
    index index.html;

    # Handle API requests
    location /api/ {
        try_files $uri $uri/ /api/index.php?$query_string;
        
        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/var/run/php/php7.4-fpm.sock; # Update PHP version if needed
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }
    }

    # For React router (SPA)
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Deny access to sensitive files
    location ~ /\.(ht|git) {
        deny all;
    }
}
```

Enable the site and restart Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/enderhost-order /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 9. Set Up the Cron Job for Expiry Reminders

Create a log directory for PHP errors:
```bash
sudo mkdir -p /var/log/php
sudo chown www-data:www-data /var/log/php
```

Add a cron job to run the expiry check script daily:
```bash
sudo crontab -e
```

Add this line to run the script at 8 AM daily:
```
0 8 * * * php /var/www/enderhost-order/api/expiry_check.php >> /var/log/php/expiry_cron.log 2>&1
```

### 10. Build and Deploy Frontend

Build the React application on your development machine:
```bash
npm run build
```

Transfer the built files to the server:
```bash
# Local command - replace with your server details
scp -r build/* user@your-server:/var/www/enderhost-order/public/
```

### 11. Set Up SSL with Let's Encrypt

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d order.enderhost.in
```

### 12. Set Up System Maintenance

Create a daily backup script:
```bash
sudo nano /etc/cron.daily/backup-enderhost-orders
```

Add the following content:
```bash
#!/bin/bash
BACKUP_DIR="/var/backups/enderhost-orders"
DATE=$(date +%Y-%m-%d)
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u enderhost_user -p'choose_a_strong_password' enderhost_orders > $BACKUP_DIR/db-$DATE.sql

# Compress backup
gzip -f $BACKUP_DIR/db-$DATE.sql

# Remove backups older than 30 days
find $BACKUP_DIR -name "db-*.sql.gz" -type f -mtime +30 -delete
```

Make the script executable:
```bash
sudo chmod +x /etc/cron.daily/backup-enderhost-orders
```

## Troubleshooting

### Database Connectivity Issues
- Check your MariaDB credentials in db_config.php
- Ensure MariaDB is running: `sudo systemctl status mariadb`
- Check if user has correct permissions: `SHOW GRANTS FOR 'enderhost_user'@'localhost';`

### Email Sending Issues
- Verify Brevo SMTP credentials
- Check server firewall for outgoing SMTP connections
- Review PHP error logs: `sudo tail -f /var/log/nginx/error.log`
- Check expiry notification logs: `sudo tail -f /var/log/php/expiry_error.log`

### Web Server Issues
- Check Nginx logs: `sudo tail -f /var/log/nginx/error.log`
- Verify file permissions: `sudo chown -R www-data:www-data /var/www/enderhost-order`
- Test Nginx configuration: `sudo nginx -t`

### Expiration Reminder Issues
- Check cron execution logs: `sudo tail -f /var/log/php/expiry_cron.log`
- Ensure the cron job is properly set up: `sudo crontab -l`
- Verify PHP execution permissions

## Maintenance Tasks

### Updating the Application
To update the application, simply rebuild the frontend and upload the new files:
```bash
# On development machine
npm run build

# Transfer files to server
scp -r build/* user@your-server:/var/www/enderhost-order/public/
```

### Database Maintenance
To access the database:
```bash
mysql -u enderhost_user -p enderhost_orders
```

Common database operations:
```sql
-- View all orders
SELECT * FROM orders;

-- View orders expiring soon
SELECT * FROM orders WHERE expiry_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY);

-- Update order status
UPDATE orders SET status = 'renewed', expiry_date = DATE_ADD(expiry_date, INTERVAL 30 DAY) WHERE order_id = 'ORDER123';

-- Check expired orders
SELECT * FROM orders WHERE status = 'expired';
```

## Security Considerations

1. Regularly update system packages: `sudo apt update && sudo apt upgrade`
2. Secure MariaDB: `sudo mysql_secure_installation`
3. Consider implementing rate limiting in Nginx to prevent abuse
4. Regularly rotate database credentials and SMTP passwords
5. Monitor logs for suspicious activity
6. Consider implementing a Web Application Firewall (WAF)
7. Encrypt sensitive data in the database if needed
