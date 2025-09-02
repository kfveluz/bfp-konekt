<?php
require_once '../config/db_connect.php';

$conn = getDBConnection();

// Add columns to users table if they don't exist
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS email VARCHAR(255) NOT NULL AFTER name");
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS created_by VARCHAR(20) DEFAULT NULL");
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS is_default_password BOOLEAN DEFAULT TRUE");

// Create user_logs table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS user_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(20) NOT NULL,
    action VARCHAR(50) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

closeDBConnection($conn);
echo "Migration completed successfully."; 