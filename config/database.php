<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'bfpkqrib_bfkuser');
define('DB_PASS', 'Kiko_195568');
define('DB_NAME', 'bfpkqrib_bfpkonekt');

try {
    // Create PDO connection
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        )
    );

    // Create users table if not exists
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id VARCHAR(20) PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        type ENUM('admin', 'user') NOT NULL,
        password VARCHAR(255) NOT NULL,
        is_default_password BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);

    // Create incidents table if not exists
    $sql = "CREATE TABLE IF NOT EXISTS incidents (
        id VARCHAR(50) PRIMARY KEY,
        type VARCHAR(50) NOT NULL,
        location VARCHAR(255) NOT NULL,
        description TEXT,
        source VARCHAR(100),
        url VARCHAR(255),
        timestamp DATETIME NOT NULL,
        status ENUM('active', 'resolved', 'false_alarm', 'non_incident') DEFAULT 'active',
        confidence INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);

    // Create settings table if not exists
    $sql = "CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(50) UNIQUE NOT NULL,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);

    // Insert default settings if not exists
    $defaultSettings = [
        ['update_interval', '1'],
        ['notification_sound', 'enabled'],
        ['keywords', 'sunog,fire,nasusunog,fire alert'],
        ['slider_interval', '3500']
    ];

    $stmt = $conn->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
    foreach ($defaultSettings as $setting) {
        $stmt->execute($setting);
    }

    // Insert default admin user if not exists
    $defaultAdmin = [
        'id' => 'BFPK0001',
        'name' => 'System Administrator',
        'type' => 'admin',
        'password' => password_hash('Kiko_195568', PASSWORD_DEFAULT),
        'is_default_password' => true
    ];

    $stmt = $conn->prepare("INSERT IGNORE INTO users (id, name, type, password, is_default_password) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $defaultAdmin['id'],
        $defaultAdmin['name'],
        $defaultAdmin['type'],
        $defaultAdmin['password'],
        $defaultAdmin['is_default_password']
    ]);

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?> 