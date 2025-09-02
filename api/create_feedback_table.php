<?php
require_once(__DIR__ . '/../config/db_connect.php');
header('Content-Type: application/json');

try {
    $conn = getDBConnection();
    
    // Create feedback table
    $sql = "CREATE TABLE IF NOT EXISTS incident_feedback (
        id INT AUTO_INCREMENT PRIMARY KEY,
        incident_id VARCHAR(10) NOT NULL,
        user_id VARCHAR(20) NOT NULL,
        rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
        response_time_rating INT CHECK (response_time_rating >= 1 AND response_time_rating <= 5),
        professionalism_rating INT CHECK (professionalism_rating >= 1 AND professionalism_rating <= 5),
        effectiveness_rating INT CHECK (effectiveness_rating >= 1 AND effectiveness_rating <= 5),
        overall_satisfaction INT CHECK (overall_satisfaction >= 1 AND overall_satisfaction <= 5),
        feedback_text TEXT,
        admin_response TEXT,
        evidence_image VARCHAR(255),
        category VARCHAR(50) DEFAULT 'general',
        status VARCHAR(20) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_incident_id (incident_id),
        INDEX idx_user_id (user_id),
        INDEX idx_status (status),
        INDEX idx_created_at (created_at)
    )";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Feedback table created successfully']);
    } else {
        throw new Exception('Failed to create feedback table: ' . $conn->error);
    }
    
} catch (Exception $e) {
    error_log("Feedback table creation error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
}
?> 