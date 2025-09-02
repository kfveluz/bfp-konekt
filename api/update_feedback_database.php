<?php
require_once(__DIR__ . '/../config/db_connect.php');
header('Content-Type: application/json');

try {
    $conn = getDBConnection();
    $results = [];
    $updatesApplied = [];
    
    // Check if feedback table exists
    $tableExists = $conn->query("SHOW TABLES LIKE 'incident_feedback'");
    
    if ($tableExists->num_rows == 0) {
        // Create the feedback table if it doesn't exist
        $createFeedbackTableSQL = "CREATE TABLE incident_feedback (
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
            evidence_image VARCHAR(500),
            image_filename VARCHAR(255),
            image_size INT,
            image_type VARCHAR(50),
            category VARCHAR(50) DEFAULT 'general',
            status VARCHAR(20) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_incident_id (incident_id),
            INDEX idx_user_id (user_id),
            INDEX idx_status (status),
            INDEX idx_created_at (created_at),
            INDEX idx_category (category)
        )";
        
        if ($conn->query($createFeedbackTableSQL)) {
            $results[] = 'Feedback table created successfully with image support';
        } else {
            throw new Exception('Failed to create feedback table: ' . $conn->error);
        }
    } else {
        // Table exists, check and add missing columns
        $updates = [];
        
        // Check if admin_response column exists first
        $result = $conn->query("SHOW COLUMNS FROM incident_feedback LIKE 'admin_response'");
        $adminResponseExists = $result->num_rows > 0;
        
        // Check if evidence_image column exists
        $result = $conn->query("SHOW COLUMNS FROM incident_feedback LIKE 'evidence_image'");
        if ($result->num_rows == 0) {
            if ($adminResponseExists) {
                $updates[] = "ADD COLUMN evidence_image VARCHAR(500) AFTER admin_response";
            } else {
                $updates[] = "ADD COLUMN evidence_image VARCHAR(500) AFTER feedback_text";
            }
        }
        
        // Check if image_filename column exists
        $result = $conn->query("SHOW COLUMNS FROM incident_feedback LIKE 'image_filename'");
        if ($result->num_rows == 0) {
            $updates[] = "ADD COLUMN image_filename VARCHAR(255) AFTER evidence_image";
        }
        
        // Check if image_size column exists
        $result = $conn->query("SHOW COLUMNS FROM incident_feedback LIKE 'image_size'");
        if ($result->num_rows == 0) {
            $updates[] = "ADD COLUMN image_size INT AFTER image_filename";
        }
        
        // Check if image_type column exists
        $result = $conn->query("SHOW COLUMNS FROM incident_feedback LIKE 'image_type'");
        if ($result->num_rows == 0) {
            $updates[] = "ADD COLUMN image_type VARCHAR(50) AFTER image_size";
        }
        
        // Check if admin_response column exists and add it if missing
        if (!$adminResponseExists) {
            $updates[] = "ADD COLUMN admin_response TEXT AFTER feedback_text";
        }
        
        // Check if category index exists
        $result = $conn->query("SHOW INDEX FROM incident_feedback WHERE Key_name = 'idx_category'");
        if ($result->num_rows == 0) {
            $updates[] = "ADD INDEX idx_category (category)";
        }
        
        // Apply updates if any
        if (!empty($updates)) {
            $alterSQL = "ALTER TABLE incident_feedback " . implode(", ", $updates);
            if ($conn->query($alterSQL)) {
                $results[] = 'Database updated successfully with image support fields';
                $updatesApplied = $updates;
            } else {
                throw new Exception('Failed to update feedback table: ' . $conn->error);
            }
        } else {
            $results[] = 'Database is already up to date with image support';
        }
    }
    
    // Check if feedback_images table exists
    $imagesTableExists = $conn->query("SHOW TABLES LIKE 'feedback_images'");
    
    if ($imagesTableExists->num_rows == 0) {
        // Create the feedback_images table if it doesn't exist
        $createImagesTableSQL = "CREATE TABLE feedback_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            filename VARCHAR(255) NOT NULL,
            filepath VARCHAR(500) NOT NULL,
            file_size INT NOT NULL,
            file_type VARCHAR(50) NOT NULL,
            uploaded_by VARCHAR(20) NOT NULL,
            uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            feedback_id INT NULL,
            INDEX idx_uploaded_by (uploaded_by),
            INDEX idx_uploaded_at (uploaded_at),
            INDEX idx_feedback_id (feedback_id),
            FOREIGN KEY (feedback_id) REFERENCES incident_feedback(id) ON DELETE SET NULL
        )";
        
        if ($conn->query($createImagesTableSQL)) {
            $results[] = 'Feedback images table created successfully';
        } else {
            throw new Exception('Failed to create feedback images table: ' . $conn->error);
        }
    } else {
        $results[] = 'Feedback images table already exists';
    }
    
    // Create uploads directory if it doesn't exist
    $uploadDir = __DIR__ . '/../uploads/feedback/';
    $uploadDirCreated = false;
    
    if (!is_dir($uploadDir)) {
        if (mkdir($uploadDir, 0755, true)) {
            $results[] = 'Upload directory created successfully';
            $uploadDirCreated = true;
        } else {
            $results[] = 'Failed to create upload directory';
        }
    } else {
        $results[] = 'Upload directory already exists';
    }
    
    // Return single JSON response
    echo json_encode([
        'success' => true,
        'message' => implode('; ', $results),
        'updates_applied' => $updatesApplied,
        'upload_dir_created' => $uploadDirCreated,
        'upload_dir_exists' => is_dir($uploadDir)
    ]);
    
} catch (Exception $e) {
    error_log("Database update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
}
?> 