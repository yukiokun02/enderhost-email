
# EnderHOST Order System

## Overview
The EnderHOST Order System allows you to collect Minecraft server orders, store customer details in MariaDB, and automate email communications. It includes automatic expiration reminders for both customers and administrators.

## Quick Setup Guide

### 1. Server Requirements
Ensure you have:
- Nginx web server
- PHP 7.4+ with php-fpm
- MariaDB database
- PHPMailer for emails

### 2. Database Setup
Log into MariaDB and create the database:
```sql
CREATE DATABASE enderhost_orders;
CREATE USER 'enderhost_user'@'localhost' IDENTIFIED BY 'your_strong_password';
GRANT ALL PRIVILEGES ON enderhost_orders.* TO 'enderhost_user'@'localhost';
FLUSH PRIVILEGES;
```

Run the database schema creation:
```sql
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
```

### 3. Application Files
1. Create a directory for the application:
```bash
mkdir -p /var/www/enderhost-order
```

2. Upload frontend files (HTML, CSS, JS) to `/var/www/enderhost-order/public/`

3. Update the database configuration in `/var/www/enderhost-order/api/db_config.php`:
```php
<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'enderhost_user');
define('DB_PASSWORD', 'your_strong_password');
define('DB_NAME', 'enderhost_orders');
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
?>
```

4. Update Brevo SMTP credentials in `/var/www/enderhost-order/api/mail_config.php`:
```php
// Inside sendOrderConfirmation() function:
$mail->Host       = 'smtp-relay.brevo.com';
$mail->Username   = 'your_brevo_username';
$mail->Password   = 'your_brevo_password';
```

### 4. Install PHPMailer
Install PHPMailer using Composer:
```bash
cd /var/www/enderhost-order/api
curl -sS https://getcomposer.org/installer | php
php composer.phar require phpmailer/phpmailer
```

### 5. Nginx Configuration
Create a new site configuration:
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

    location /api/ {
        try_files $uri $uri/ /api/index.php?$query_string;
        
        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/var/run/php/php7.4-fpm.sock; # Update PHP version if needed
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }
    }

    location / {
        try_files $uri $uri/ /index.html;
    }

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

### 6. Set Up Expiry Notification System

1. Create a log directory:
```bash
sudo mkdir -p /var/log/php
sudo chown www-data:www-data /var/log/php
```

2. Add a cron job to run the expiry notification script daily:
```bash
sudo crontab -e
```

Add this line:
```
0 8 * * * php /var/www/enderhost-order/api/expiry_check.php >> /var/log/php/expiry_cron.log 2>&1
```

### 7. Set Up SSL (Optional but Recommended)
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d order.enderhost.in
```

## Troubleshooting Tips

### Email Issues
- Verify your Brevo SMTP credentials
- Check PHP error logs: `sudo tail -f /var/log/nginx/error.log`
- Check expiry cron logs: `sudo tail -f /var/log/php/expiry_cron.log`

### Database Connectivity
- Verify MariaDB credentials and permissions
- Check if MariaDB is running: `sudo systemctl status mariadb`

### Web Server Issues
- Check Nginx logs: `sudo tail -f /var/log/nginx/error.log`
- Test Nginx configuration: `sudo nginx -t`
- Check file permissions: `sudo chown -R www-data:www-data /var/www/enderhost-order`
