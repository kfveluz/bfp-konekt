<?php
require_once(__DIR__ . '/../config/db_connect.php');
header('Content-Type: application/json');

try {
    $conn = getDBConnection();
    $results = [];

    // Create incident_evidence table if not exists
    $sql = "CREATE TABLE IF NOT EXISTS incident_evidence (
        id INT AUTO_INCREMENT PRIMARY KEY,
        incident_id VARCHAR(50) NOT NULL,
        user_id VARCHAR(20) NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_incident_id (incident_id),
        INDEX idx_user_id (user_id),
        INDEX idx_created_at (created_at)
    )";
    if ($conn->query($sql)) {
        $results[] = 'incident_evidence table exists or created successfully.';
    } else {
        throw new Exception('Failed to create incident_evidence table: ' . $conn->error);
    }

    // Drop feedback_images table if exists
    $sql = "DROP TABLE IF EXISTS feedback_images";
    if ($conn->query($sql)) {
        $results[] = 'feedback_images table dropped (if existed).';
    } else {
        throw new Exception('Failed to drop feedback_images table: ' . $conn->error);
    }

    // Drop incident_feedback table if exists
    $sql = "DROP TABLE IF EXISTS incident_feedback";
    if ($conn->query($sql)) {
        $results[] = 'incident_feedback table dropped (if existed).';
    } else {
        throw new Exception('Failed to drop incident_feedback table: ' . $conn->error);
    }

    echo json_encode(['success' => true, 'message' => implode(' ', $results)]);
} catch (Exception $e) {
    error_log('Update evidence database error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
}
?> 