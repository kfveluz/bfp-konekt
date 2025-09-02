<?php
session_start();
require_once(__DIR__ . '/../config/db_connect.php');
header('Content-Type: application/json');

// Allow access to logged-in users
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Please log in first.']);
    exit;
}

try {
    $feedbackId = $_GET['feedback_id'] ?? null;
    $imageId = $_GET['image_id'] ?? null;
    
    if (!$feedbackId && !$imageId) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameter: feedback_id or image_id']);
        exit;
    }
    
    $conn = getDBConnection();
    
    if ($feedbackId) {
        // Get image by feedback ID
        $stmt = $conn->prepare("
            SELECT fi.*, f.incident_id, f.user_id as feedback_user_id 
            FROM feedback_images fi 
            LEFT JOIN incident_feedback f ON fi.feedback_id = f.id 
            WHERE fi.feedback_id = ?
        ");
        $stmt->bind_param('i', $feedbackId);
    } else {
        // Get image by image ID
        $stmt = $conn->prepare("
            SELECT fi.*, f.incident_id, f.user_id as feedback_user_id 
            FROM feedback_images fi 
            LEFT JOIN incident_feedback f ON fi.feedback_id = f.id 
            WHERE fi.id = ?
        ");
        $stmt->bind_param('i', $imageId);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $imageData = $result->fetch_assoc();
        
        // Check if file exists
        $fullPath = __DIR__ . '/../' . $imageData['filepath'];
        $imageData['file_exists'] = file_exists($fullPath);
        $imageData['full_path'] = $fullPath;
        
        // Add file URL for display
        $imageData['display_url'] = $imageData['filepath'];
        
        echo json_encode([
            'success' => true,
            'image' => $imageData
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Image not found']);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Get feedback image error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error. Please try again later.']);
}
?> 