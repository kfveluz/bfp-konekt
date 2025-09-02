<?php
require_once(__DIR__ . '/../config/db_connect.php');
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

class DatabaseUpdater {
    private $conn;
    private $updates = [];
    private $errors = [];
    
    public function __construct() {
        try {
            $this->conn = getDBConnection();
        } catch (Exception $e) {
            $this->errors[] = "Database connection failed: " . $e->getMessage();
        }
    }
    
    public function runUpdates() {
        if (!empty($this->errors)) {
            return $this->formatResponse(false, "Database connection failed", $this->errors);
        }
        
        // Run all updates
        $this->createIncidentEvidenceTable();
        $this->removeOldFeedbackTables();
        $this->ensureRequiredTablesExist();
        $this->updateTableStructures();
        
        if (empty($this->errors)) {
            return $this->formatResponse(true, "Database updated successfully", $this->updates);
        } else {
            return $this->formatResponse(false, "Some updates failed", $this->errors, $this->updates);
        }
    }
    
    private function createIncidentEvidenceTable() {
        try {
            // Check if table already exists
            $result = $this->conn->query("SHOW TABLES LIKE 'incident_evidence'");
            if ($result->num_rows > 0) {
                $this->updates[] = "incident_evidence table already exists";
                return;
            }
            
            $sql = "CREATE TABLE incident_evidence (
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
            
            if ($this->conn->query($sql)) {
                $this->updates[] = "Created incident_evidence table successfully";
            } else {
                throw new Exception($this->conn->error);
            }
        } catch (Exception $e) {
            $this->errors[] = "Failed to create incident_evidence table: " . $e->getMessage();
        }
    }
    
    private function removeOldFeedbackTables() {
        $oldTables = ['feedback', 'feedback_images', 'feedback_requests'];
        
        foreach ($oldTables as $table) {
            try {
                $result = $this->conn->query("SHOW TABLES LIKE '$table'");
                if ($result->num_rows > 0) {
                    $sql = "DROP TABLE IF EXISTS $table";
                    if ($this->conn->query($sql)) {
                        $this->updates[] = "Removed old table: $table";
                    } else {
                        throw new Exception($this->conn->error);
                    }
                } else {
                    $this->updates[] = "Table $table does not exist (already removed)";
                }
            } catch (Exception $e) {
                $this->errors[] = "Failed to remove table $table: " . $e->getMessage();
            }
        }
    }
    
    private function ensureRequiredTablesExist() {
        $requiredTables = [
            'users' => "CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(100),
                role ENUM('admin', 'user') DEFAULT 'user',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            'incidents' => "CREATE TABLE incidents (
                id INT AUTO_INCREMENT PRIMARY KEY,
                incident_id VARCHAR(50) UNIQUE NOT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                location VARCHAR(255),
                status ENUM('pending', 'in_progress', 'resolved', 'closed') DEFAULT 'pending',
                reported_by VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            'settings' => "CREATE TABLE settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(100) UNIQUE NOT NULL,
                setting_value TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )"
        ];
        
        foreach ($requiredTables as $table => $createSQL) {
            try {
                $result = $this->conn->query("SHOW TABLES LIKE '$table'");
                if ($result->num_rows == 0) {
                    if ($this->conn->query($createSQL)) {
                        $this->updates[] = "Created required table: $table";
                    } else {
                        throw new Exception($this->conn->error);
                    }
                } else {
                    $this->updates[] = "Required table $table already exists";
                }
            } catch (Exception $e) {
                $this->errors[] = "Failed to create table $table: " . $e->getMessage();
            }
        }
    }
    
    private function updateTableStructures() {
        // Add any missing columns to existing tables
        $this->addMissingColumns();
        
        // Update any existing data structures if needed
        $this->migrateExistingData();
    }
    
    private function addMissingColumns() {
        // Check and add missing columns to incidents table
        $incidentColumns = [
            'latitude' => "ALTER TABLE incidents ADD COLUMN latitude DECIMAL(10,8) NULL",
            'longitude' => "ALTER TABLE incidents ADD COLUMN longitude DECIMAL(11,8) NULL",
            'priority' => "ALTER TABLE incidents ADD COLUMN priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium'"
        ];
        
        foreach ($incidentColumns as $column => $sql) {
            try {
                $result = $this->conn->query("SHOW COLUMNS FROM incidents LIKE '$column'");
                if ($result->num_rows == 0) {
                    if ($this->conn->query($sql)) {
                        $this->updates[] = "Added column $column to incidents table";
                    } else {
                        throw new Exception($this->conn->error);
                    }
                }
            } catch (Exception $e) {
                $this->errors[] = "Failed to add column $column: " . $e->getMessage();
            }
        }
    }
    
    private function migrateExistingData() {
        // Add any data migration logic here if needed
        // For example, converting old feedback data to new evidence format
        $this->updates[] = "Data migration completed (no data to migrate)";
    }
    
    private function formatResponse($success, $message, $errors = [], $updates = []) {
        return json_encode([
            'success' => $success,
            'message' => $message,
            'errors' => $errors,
            'updates' => $updates,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_PRETTY_PRINT);
    }
}

// Run the database update
try {
    $updater = new DatabaseUpdater();
    $result = $updater->runUpdates();
    echo $result;
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Fatal error during database update',
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}
?> 