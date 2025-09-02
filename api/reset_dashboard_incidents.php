<?php
require_once __DIR__ . '/../config/db_connect.php';
header('Content-Type: application/json');

try {
    $conn = getDBConnection();
    $result = $conn->query("DELETE FROM incidents");
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'All dashboard incidents have been reset (all rows deleted from incidents table)']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to reset dashboard incidents: ' . $conn->error]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} 