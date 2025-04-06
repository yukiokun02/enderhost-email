
<?php
// This script checks if the authentication system is set up properly
// And creates necessary directories and tables

// Include database configuration
require_once '../config/db_config.php';

// Create logs directory if it doesn't exist
$logsDir = __DIR__ . '/../logs';
if (!file_exists($logsDir)) {
    mkdir($logsDir, 0755, true);
    file_put_contents($logsDir . '/.htaccess', "# Deny access to all files in this directory\n<FilesMatch \".*\">\n    Order Allow,Deny\n    Deny from all\n</FilesMatch>");
    echo "Created logs directory\n";
}

// Create users table if it doesn't exist
$checkTableSql = "SHOW TABLES LIKE 'users'";
$tableResult = mysqli_query($conn, $checkTableSql);

if (mysqli_num_rows($tableResult) == 0) {
    // Create the users table
    $createTableSql = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (mysqli_query($conn, $createTableSql)) {
        echo "Created users table\n";
        
        // Create default admin user
        $defaultUsername = 'admin';
        $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
        
        $insertAdminSql = "INSERT INTO users (username, password) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $insertAdminSql);
        mysqli_stmt_bind_param($stmt, "ss", $defaultUsername, $defaultPassword);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "Created default admin user (username: admin, password: admin123)\n";
        } else {
            echo "Error creating default admin user: " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "Error creating users table: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "Users table already exists\n";
}

// Close connection
mysqli_close($conn);

echo "\nAuthentication system initialization complete.\n";
echo "Please change the default admin password after first login.\n";
