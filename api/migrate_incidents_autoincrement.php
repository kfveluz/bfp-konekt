<?php
header('Content-Type: application/json');
require_once '../config/db_connect.php';

$response = ['success' => false, 'message' => '', 'details' => []];

try {
    $conn = getDBConnection();

    // Check current id column type
    $result = $conn->query("SHOW COLUMNS FROM incidents WHERE Field = 'id'");
    $row = $result->fetch_assoc();
    $currentType = $row ? $row['Type'] : '';
    $isAutoIncrement = strpos($row['Extra'], 'auto_increment') !== false;

    // Only run if not already INT AUTO_INCREMENT
    if (strtolower($currentType) !== 'int(11)' || !$isAutoIncrement) {
        // Drop primary key if not on id
        $conn->query("ALTER TABLE incidents DROP PRIMARY KEY");
        // Change id to INT AUTO_INCREMENT PRIMARY KEY
        $conn->query("ALTER TABLE incidents MODIFY COLUMN id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY");
        $response['details'][] = 'id column changed to INT AUTO_INCREMENT PRIMARY KEY.';
    } else {
        $response['details'][] = 'id column already INT AUTO_INCREMENT PRIMARY KEY.';
    }

    $response['success'] = true;
    $response['message'] = 'Migration completed.';
    closeDBConnection($conn);
} catch (Exception $e) {
    $response['message'] = 'Migration failed: ' . $e->getMessage();
    $response['details'][] = $e->getTraceAsString();
}

echo json_encode($response, JSON_PRETTY_PRINT); 