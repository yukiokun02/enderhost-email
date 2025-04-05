
# EnderHOST Order System

## Overview
The EnderHOST Order System allows you to collect Minecraft server orders, store customer details in MariaDB, and automate email communications. It includes automatic expiration reminders for both customers and administrators.

## Directory Structure
```
/var/www/enderhost-order/
├── public/               # Frontend files
├── api/                  # Backend API
│   ├── config/           # Configuration files
│   │   ├── db_config.php     # Database connection
│   │   └── mail_config.php   # Email settings
│   ├── orders/           # Order management
│   │   └── create_order.php  # Create new orders
│   └── notifications/    # Notification system
│       └── expiry_check.php  # Expiry checks and notifications
├── vendor/               # Composer dependencies
└── sql/                  # SQL scripts
    └── database_schema.sql   # Database schema
```

## Quick Setup Guide

### 1. Server Requirements
Ensure you have:
- Nginx web server
- PHP 7.4+ with php-fpm
- MariaDB database
- Composer (for installing PHPMailer)

### 2. Database Setup
Run the SQL script:
```bash
mysql -u root -p < /var/www/enderhost-order/sql/database_schema.sql
```

Alternatively, you can manually run the commands in the SQL file:
```sql
CREATE DATABASE enderhost_orders;
CREATE USER 'enderhost_user'@'localhost' IDENTIFIED BY 'your_strong_password';
GRANT ALL PRIVILEGES ON enderhost_orders.* TO 'enderhost_user'@'localhost';
FLUSH PRIVILEGES;

USE enderhost_orders;

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

### 3. Application Setup
1. Create the directory structure:
```bash
mkdir -p /var/www/enderhost-order/{public,api/{config,orders,notifications},sql,vendor}
```

2. Upload files to the appropriate directories as shown in the directory structure above.

3. Update database and email configurations:
   - Edit `/var/www/enderhost-order/api/config/db_config.php` with your database credentials
   - Edit `/var/www/enderhost-order/api/config/mail_config.php` with your Brevo SMTP credentials

### 4. Install PHPMailer
Install PHPMailer using Composer:
```bash
cd /var/www/enderhost-order
composer require phpmailer/phpmailer
```

### 5. Nginx Configuration
Create a new Nginx site configuration:
```bash
sudo nano /etc/nginx/sites-available/enderhost-order
```

Add the following configuration:
```nginx
server {
    listen 80;
    server_name order.enderhost.in;  # Replace with your domain
    root /var/www/enderhost-order/public;
    index index.html;

    location /api/ {
        try_files $uri $uri/ /index.php$is_args$args;
        
        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;  # Update PHP version if needed
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
0 8 * * * php /var/www/enderhost-order/api/notifications/expiry_check.php >> /var/log/php/expiry_cron.log 2>&1
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
- Check file permissions: 
```bash
sudo chown -R www-data:www-data /var/www/enderhost-order
sudo find /var/www/enderhost-order -type d -exec chmod 755 {} \;
sudo find /var/www/enderhost-order -type f -exec chmod 644 {} \;
```
