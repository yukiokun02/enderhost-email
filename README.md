
# EnderHOST Order System

## Overview
The EnderHOST Order System allows you to collect Minecraft server orders, store customer details in MariaDB, and automate email communications. It includes automatic expiration reminders for both customers and administrators.

## Directory Structure
```
/
├── public/               # Frontend files
│   ├── index.html        # Main HTML file
│   ├── css/              # CSS files
│   └── js/               # JavaScript files
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
- Nginx web server
- PHP 7.4+ with php-fpm
- MariaDB database
- Composer (for installing PHPMailer)

### 2. Database Setup
Run the SQL script:
```bash
mysql -u root -p < sql/database_schema.sql
```

### 3. Application Setup
1. Upload all the files to your web server.

2. Update database and email configurations:
   - Edit `api/config/db_config.php` with your database credentials
   - Edit `api/config/mail_config.php` with your Brevo SMTP credentials

### 4. Install PHPMailer
Install PHPMailer using Composer:
```bash
composer require phpmailer/phpmailer
```

### 5. Nginx Configuration
Create a new Nginx site configuration:
```
server {
    listen 80;
    server_name order.enderhost.in;  # Replace with your domain
    root /path/to/your/website/public;
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

### 6. Set Up Expiry Notification System
1. Create a log directory:
```bash
mkdir -p /var/log/php
chown www-data:www-data /var/log/php
```

2. Add a cron job to run the expiry notification script daily:
```bash
crontab -e
```

Add this line:
```
0 8 * * * php /path/to/your/website/api/notifications/expiry_check.php >> /var/log/php/expiry_cron.log 2>&1
```

### 7. Set Up SSL (Optional but Recommended)
```bash
apt install certbot python3-certbot-nginx
certbot --nginx -d order.enderhost.in
```

## Troubleshooting Tips

### Email Issues
- Verify your Brevo SMTP credentials
- Check PHP error logs: `tail -f /var/log/nginx/error.log`
- Check expiry cron logs: `tail -f /var/log/php/expiry_cron.log`

### Database Connectivity
- Verify MariaDB credentials and permissions
- Check if MariaDB is running: `systemctl status mariadb`

### Web Server Issues
- Check Nginx logs: `tail -f /var/log/nginx/error.log`
- Test Nginx configuration: `nginx -t`
- Check file permissions: 
```bash
chown -R www-data:www-data /path/to/your/website
find /path/to/your/website -type d -exec chmod 755 {} \;
find /path/to/your/website -type f -exec chmod 644 {} \;
```
