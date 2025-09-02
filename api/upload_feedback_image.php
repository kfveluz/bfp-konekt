<?php
session_start();
require_once(__DIR__ . '/../config/db_connect.php');
header('Content-Type: application/json');

// Restrict to logged-in admin or BFP users only
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['admin', 'bfp'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Only admin and BFP users can upload images.']);
    exit;
}

try {
    // Check if file was uploaded
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'No image file uploaded or upload error occurred.']);
        exit;
    }

    $file = $_FILES['image'];
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF images are allowed.']);
        exit;
    }
    
    // Validate file size (max 5MB)
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'File size too large. Maximum size is 5MB.']);
        exit;
    }
    
    // Create upload directory if it doesn't exist
    $uploadDir = __DIR__ . '/../uploads/feedback/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'feedback_' . time() . '_' . $_SESSION['user_id'] . '.' . $fileExtension;
    $filepath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Return the relative URL for database storage
        $imageUrl = 'uploads/feedback/' . $filename;
        
        // Store image metadata in database
        $conn = getDBConnection();
        $stmt = $conn->prepare("INSERT INTO feedback_images (filename, filepath, file_size, file_type, uploaded_by, uploaded_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param('sssss', $filename, $imageUrl, $file['size'], $file['type'], $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $imageId = $conn->insert_id;
            echo json_encode([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'image_url' => $imageUrl,
                'image_id' => $imageId,
                'filename' => $filename,
                'file_size' => $file['size'],
                'file_type' => $file['type']
            ]);
        } else {
            // Image uploaded but database insert failed
            unlink($filepath); // Clean up the file
            throw new Exception('Failed to save image metadata to database');
        }
        $stmt->close();
    } else {
        throw new Exception('Failed to move uploaded file');
    }
    
} catch (Exception $e) {
    error_log("Feedback image upload error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error. Please try again later.']);
}
?> 