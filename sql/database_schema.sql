
-- Create database and user
CREATE DATABASE IF NOT EXISTS orderdb;
CREATE USER IF NOT EXISTS 'orderadmin'@'localhost' IDENTIFIED BY 'CODENAMEorder@';
GRANT ALL PRIVILEGES ON orderdb.* TO 'orderadmin'@'localhost';
FLUSH PRIVILEGES;

-- Switch to the database
USE orderdb;

-- Create orders table
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

-- Create users table with user_group field
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    user_group ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create index for faster queries
CREATE INDEX idx_order_id ON orders (order_id);
CREATE INDEX idx_expiry_date ON orders (expiry_date);
CREATE INDEX idx_status ON orders (status);
CREATE INDEX idx_user_group ON users (user_group);

