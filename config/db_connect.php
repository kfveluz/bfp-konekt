<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'bfpkqrib_bfkuser');
define('DB_PASS', 'Kiko_195568');
define('DB_NAME', 'bfpkqrib_bfpkonekt');

function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, 3306);
        
        if ($conn->connect_error) {
            error_log('Database connection failed: ' . $conn->connect_error);
            throw new Exception('Database connection failed: ' . $conn->connect_error);
        }
        
        // Set charset to ensure proper encoding
        if (!$conn->set_charset("utf8mb4")) {
            error_log('Error setting charset: ' . $conn->error);
            throw new Exception('Error setting charset: ' . $conn->error);
        }
        // Set MySQL session timezone to Asia/Manila
        if (!$conn->query("SET time_zone = '+08:00'")) {
            error_log('Error setting timezone: ' . $conn->error);
        }
    }
    
    return $conn;
}

function closeDBConnection($conn) {
    if ($conn && $conn !== null) {
        $conn->close();
        $conn = null;
    }
}

// Function to check if tables exist
function checkTables() {
    $conn = getDBConnection();
    $tables = ['users', 'incidents', 'settings'];
    $missing = [];
    
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows == 0) {
            $missing[] = $table;
        }
    }
    
    return $missing;
}

// Function to initialize database if needed
function initializeDatabase() {
    require_once 'database.php'; // This will create tables if they don't exist
}

// Check and initialize if needed
$missingTables = checkTables();
if (!empty($missingTables)) {
    initializeDatabase();
}
?> 