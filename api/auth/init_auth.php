
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

// Check if the users table exists and has the user_group column
$checkTableSql = "SHOW TABLES LIKE 'users'";
$tableResult = mysqli_query($conn, $checkTableSql);

if (mysqli_num_rows($tableResult) == 0) {
    // Create the users table with user_group field
    $createTableSql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        user_group ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (mysqli_query($conn, $createTableSql)) {
        echo "Created users table\n";
        
        // Create default admin user
        $defaultUsername = 'admin';
        $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $defaultGroup = 'admin';
        
        $insertAdminSql = "INSERT INTO users (username, password, user_group) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insertAdminSql);
        mysqli_stmt_bind_param($stmt, "sss", $defaultUsername, $defaultPassword, $defaultGroup);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "Created default admin user (username: admin, password: admin123)\n";
            file_put_contents($logsDir . '/auth.log', date('Y-m-d H:i:s') . ": Created default admin user with admin privileges\n", FILE_APPEND);
        } else {
            echo "Error creating default admin user: " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "Error creating users table: " . mysqli_error($conn) . "\n";
    }
} else {
    // Check if user_group column exists
    $checkColumnSql = "SHOW COLUMNS FROM users LIKE 'user_group'";
    $columnResult = mysqli_query($conn, $checkColumnSql);
    
    if (mysqli_num_rows($columnResult) == 0) {
        // Add user_group column to the users table
        $addColumnSql = "ALTER TABLE users ADD COLUMN user_group ENUM('admin', 'staff') NOT NULL DEFAULT 'staff'";
        
        if (mysqli_query($conn, $addColumnSql)) {
            echo "Added user_group column to users table\n";
            
            // Set the 'admin' user to have admin privileges
            $updateAdminSql = "UPDATE users SET user_group = 'admin' WHERE username = 'admin'";
            
            if (mysqli_query($conn, $updateAdminSql)) {
                echo "Updated admin user to have admin privileges\n";
            } else {
                echo "Error updating admin user: " . mysqli_error($conn) . "\n";
            }
        } else {
            echo "Error adding user_group column: " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "Users table with user_group column already exists\n";
    }
    
    // Verify if admin user exists, if not create it
    $checkAdminSql = "SELECT id FROM users WHERE username = 'admin'";
    $adminResult = mysqli_query($conn, $checkAdminSql);
    
    if (mysqli_num_rows($adminResult) == 0) {
        // Create default admin user
        $defaultUsername = 'admin';
        $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $defaultGroup = 'admin';
        
        $insertAdminSql = "INSERT INTO users (username, password, user_group) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insertAdminSql);
        mysqli_stmt_bind_param($stmt, "sss", $defaultUsername, $defaultPassword, $defaultGroup);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "Created default admin user (username: admin, password: admin123)\n";
            file_put_contents($logsDir . '/auth.log', date('Y-m-d H:i:s') . ": Created default admin user with admin privileges\n", FILE_APPEND);
        } else {
            echo "Error creating default admin user: " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "Admin user already exists\n";
    }
}

// Close connection
mysqli_close($conn);

echo "\nAuthentication system initialization complete.\n";
echo "Please change the default admin password after first login.\n";
