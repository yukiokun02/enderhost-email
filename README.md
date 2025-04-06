
# EnderHOST Order System

## Overview
The EnderHOST Order System allows you to collect Minecraft server orders, store customer details in MariaDB, and automate email communications. It includes automatic expiration reminders for both customers and administrators.

## Security Features
- **Staff-Only Access**: Protected login system restricts access to authorized users only
- **Admin User Management**: Administrators can create and manage staff accounts
- **Session Management**: Secure PHP session management with 30-minute inactivity timeout
- **No Public Registration**: New accounts can only be created by existing administrators

## Directory Structure
```
/
├── public/               # Static assets
├── src/                  # React frontend source
│   ├── components/       # Reusable components
│   ├── hooks/            # Custom React hooks
│   └── pages/            # Application pages
├── api/                  # Backend API files
│   ├── auth/             # Authentication system
│   │   ├── login.php         # User login
│   │   ├── logout.php        # User logout
│   │   ├── check_session.php # Session validation
│   │   └── manage_user.php   # User management 
│   ├── config/           # Configuration files
│   │   ├── db_config.php     # Database connection
│   │   └── mail_config.php   # Email settings
│   ├── orders/           # Order management
│   │   └── create_order.php  # Create new orders
│   └── notifications/    # Notification system
│       └── expiry_check.php  # Expiry checks and notifications
└── sql/                  # SQL scripts
    └── database_schema.sql   # Database schema
```

## Quick Setup Guide

### 1. Server Requirements
- Nginx web server
- PHP 7.4+ with php-fpm
- MariaDB database
- Node.js 18+ and npm
- Composer (for installing PHPMailer)

### 2. Database Setup
Run the SQL script:
```bash
mysql -u root -p < sql/database_schema.sql
```

### 3. Frontend Build
1. Install dependencies and build the React application:
```bash
npm install
npm run build
```

This will create a `dist` directory with optimized production files.

2. Set proper permissions:
```bash
chmod -R 755 dist
```

### 4. Backend Setup
1. Initialize the authentication system:
```bash
php api/auth/init_auth.php
```

This will create:
- The `users` database table
- Default admin user with credentials:
  - Username: `admin`
  - Password: `admin123` (change this after first login)
- Required log directories

2. Install PHPMailer using Composer:
```bash
composer require phpmailer/phpmailer
```

3. Configuration is pre-set with the following:
   - Database: `orderdb` with user `orderadmin`
   - SMTP: Brevo service with credentials pre-configured
   - Admin email: mail.enderhost@gmail.com

### 5. Nginx Configuration
Create a new Nginx site configuration:
```
server {
    listen 80;
    server_name order.enderhost.in;  # Replace with your domain
    
    # Frontend - static files from the build
    root /path/to/your/website/dist;
    index index.html;
    
    # Backend API
    location /api/ {
        alias /path/to/your/website/api/;
        
        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_param SCRIPT_FILENAME $request_filename;
            fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;  # Update PHP version if needed
            include fastcgi_params;
        }
    }
    
    # Handle routing for SPA
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

## Authentication System

### Default Admin Access
- Username: `admin`
- Password: `admin123`

**Important:** Change the default admin password after your first login.

### User Management
- Only authenticated administrators can create new user accounts
- Passwords are securely hashed using PHP's password_hash function
- Each user can change their own password or reset others' passwords
- Activity logs are maintained for all authentication events

### Security Considerations
- The system implements PHP session-based authentication
- Sessions expire after 30 minutes of inactivity
- Authentication logs record login attempts and admin actions
- All password changes and user management actions are logged

## Troubleshooting Tips

### Login Issues
- Check authentication logs: `tail -f /var/log/php/auth.log`
- Verify database credentials in `api/config/db_config.php`
- Reset admin password using a direct database command if needed:
```sql
UPDATE users SET password = PASSWORD_HASH('your_new_password', PASSWORD_DEFAULT) WHERE username = 'admin';
```

### Email Issues
- Verify your Brevo SMTP credentials
- Check PHP error logs: `tail -f /var/log/nginx/error.log`
- Check expiry cron logs: `tail -f /var/log/php/expiry_cron.log`

### Database Connectivity
- Verify MariaDB credentials and permissions
- Check if MariaDB is running: `systemctl status mariadb`

### Frontend Issues
- Check for build errors in the npm build output
- Verify that the Nginx configuration points to the correct dist directory
- Check browser console for JavaScript errors
