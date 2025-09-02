<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/../config/db_connect.php');

header('Content-Type: application/json');

// Check if user is logged in and is an admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if files were uploaded
if (!isset($_FILES['images'])) {
    echo json_encode(['success' => false, 'message' => 'No files uploaded']);
    exit;
}

try {
    $conn = getDBConnection();
    
    // Create slider_images table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS slider_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        url VARCHAR(255) NOT NULL,
        display_order INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error creating table: " . $conn->error);
    }
    
    $uploadedFiles = $_FILES['images'];
    $successCount = 0;
    $errors = [];
    $uploadDir = __DIR__ . '/../uploads/slider/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Get next display order
    $result = $conn->query('SELECT MAX(display_order) as max_order FROM slider_images');
    $row = $result ? $result->fetch_assoc() : null;
    $nextOrder = ($row && $row['max_order']) ? $row['max_order'] + 1 : 1;
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    
    for ($i = 0; $i < count($uploadedFiles['name']); $i++) {
        $fileName = $uploadedFiles['name'][$i];
        $fileTmpName = $uploadedFiles['tmp_name'][$i];
        $fileType = $uploadedFiles['type'][$i];
        $fileError = $uploadedFiles['error'][$i];
        
        if ($fileError !== UPLOAD_ERR_OK) {
            $errors[] = "Error uploading $fileName";
            continue;
        }
        
        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = "$fileName is not a valid image file";
            continue;
        }
        
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $newFileName = uniqid('slider_', true) . '.' . $fileExtension;
        $targetPath = $uploadDir . $newFileName;
        
        if (move_uploaded_file($fileTmpName, $targetPath)) {
            $relativePath = 'uploads/slider/' . $newFileName;
            $stmt = $conn->prepare('INSERT INTO slider_images (url, display_order) VALUES (?, ?)');
            $stmt->bind_param('si', $relativePath, $nextOrder);
            $stmt->execute();
            $stmt->close();
            $nextOrder++;
            $successCount++;
        } else {
            $errors[] = "Failed to save $fileName";
        }
    }
    
    $response = [
        'success' => $successCount > 0,
        'message' => "Uploaded $successCount images." . (count($errors) ? ' Errors: ' . implode(', ', $errors) : ''),
    ];
    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} 