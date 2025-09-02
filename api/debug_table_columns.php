<?php
require_once(__DIR__ . '/../config/db_connect.php');
header('Content-Type: application/json');

try {
    $conn = getDBConnection();
    
    // Check if feedback table exists
    $tableExists = $conn->query("SHOW TABLES LIKE 'incident_feedback'");
    
    if ($tableExists->num_rows == 0) {
        echo json_encode([
            'success' => false,
            'message' => 'incident_feedback table does not exist'
        ]);
        exit;
    }
    
    // Get all column names
    $result = $conn->query("SHOW COLUMNS FROM incident_feedback");
    $columns = [];
    
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    // Also check users table structure
    $usersTableExists = $conn->query("SHOW TABLES LIKE 'users'");
    $usersColumns = [];
    
    if ($usersTableExists->num_rows > 0) {
        $usersResult = $conn->query("SHOW COLUMNS FROM users");
        while ($row = $usersResult->fetch_assoc()) {
            $usersColumns[] = $row['Field'];
        }
    }
    
    echo json_encode([
        'success' => true,
        'incident_feedback_columns' => $columns,
        'users_columns' => $usersColumns,
        'feedback_table_exists' => true,
        'users_table_exists' => $usersTableExists->num_rows > 0
    ]);
    
} catch (Exception $e) {
    error_log("Debug table columns error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'System error: ' . $e->getMessage()
    ]);
}
?> 