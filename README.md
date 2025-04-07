
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

## Step-by-Step Setup Guide

### 1. Server Requirements
- Nginx or Apache web server
- PHP 7.4+ with php-fpm and mysqli extension
- MariaDB/MySQL database
- Node.js 18+ and npm
- Composer (for installing PHPMailer)

### 2. Database Setup
1. Import the database schema:
```bash
mysql -u root -p < sql/database_schema.sql
```

2. Verify the database was created properly:
```bash
mysql -u root -p -e "SHOW DATABASES;" | grep orderdb
```

3. Confirm the admin user was created:
```bash
mysql -u root -p -e "SELECT username, user_group FROM orderdb.users;"
```

### 3. Frontend Build
1. Clone the repository and navigate to the project:
```bash
git clone [repository-url]
cd enderhost-order-system
```

2. Install dependencies and build the React application:
```bash
npm install
npm run build
```

3. Set proper permissions for the build directory:
```bash
chmod -R 755 dist
```

### 4. Authentication System Setup
1. Create the logs directory for authentication logs:
```bash
mkdir -p api/logs
chmod 755 api/logs
```

2. Initialize the authentication system:
```bash
php api/auth/init_auth.php
```

This will:
- Create the `users` database table if it doesn't exist
- Ensure the default admin account exists with:
  - Username: `admin`
  - Password: `admin123`

3. **IMPORTANT**: Change the default admin password after first login!

### 5. Web Server Configuration

#### For Nginx:
Create a new site configuration in `/etc/nginx/sites-available/`:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/your/project/dist;
    index index.html index.php;

    # Frontend - static files
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Backend API
    location /api/ {
        try_files $uri $uri/ /api/index.php?$query_string;
        
        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;  # Update PHP version if needed
        }
    }

    # Deny access to sensitive files
    location ~ /\.(git|htaccess|env) {
        deny all;
    }
}
```

Enable the site:
```bash
ln -s /etc/nginx/sites-available/your-domain.com /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

#### For Apache:
Create an .htaccess file in your project root:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # API requests pass through to PHP
    RewriteRule ^api/(.*)$ api/$1 [L]
    
    # SPA routing - redirect to index.html
    RewriteRule ^index\.html$ - [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . /index.html [L]
</IfModule>
```

### 6. Finalize Configuration

1. Set up the notification system for order expiry reminders:
```bash
crontab -e
```

Add this line to run the expiry check daily at 8 AM:
```
0 8 * * * php /path/to/your/project/api/notifications/expiry_check.php >> /var/log/php/expiry_check.log 2>&1
```

2. Create the log directory:
```bash
mkdir -p /var/log/php
chmod 755 /var/log/php
```

### 7. First Login

1. Access your application at http://your-domain.com
2. Log in with the default credentials:
   - Username: `admin`
   - Password: `admin123`
3. Immediately navigate to the User Management page to:
   - Change the default admin password
   - Create additional staff accounts as needed

## User Management Instructions

### Managing Users
1. Login as an administrator
2. Click on the "Users" link in the header
3. From the User Management page, you can:
   - Create new staff accounts
   - Reset passwords for existing users
   - Delete user accounts (cannot delete your own account)

### Password Security Guidelines
- Use strong passwords with at least 8 characters
- Include a mix of uppercase, lowercase, numbers, and special characters
- Change passwords regularly
- Each staff member should have their own account (no shared accounts)

## Troubleshooting

### Login Issues
- Verify database connection in `api/config/db_config.php`
- Check authentication logs: `tail -f api/logs/auth.log`
- Check the admin user exists with the correct password hash:
  ```sql
  SELECT * FROM users WHERE username = 'admin';
  ```
- If the admin user doesn't exist or login fails, run:
  ```bash
  php api/auth/init_auth.php
  ```
- Ensure sessions are working properly: `php -i | grep session`

### Common Database Issues
- Make sure MariaDB/MySQL service is running: `systemctl status mysql`
- Check database connection details in `api/config/db_config.php`
- Verify the database user has proper permissions

### Server Configuration Issues
- Check web server error logs:
  - Nginx: `tail -f /var/log/nginx/error.log`
  - Apache: `tail -f /var/log/apache2/error.log`
- Verify PHP is configured properly: `php -v`

## Security Considerations

- **Change Default Password**: Immediately change the default admin password after first login
- **Regular Updates**: Keep PHP, MariaDB, and all dependencies up to date
- **Backup Regularly**: Set up regular database backups
- **Monitor Logs**: Check authentication logs regularly for suspicious activity
- **HTTPS**: For production, always use HTTPS with a valid SSL certificate

For additional assistance, contact the EnderHOST development team.
