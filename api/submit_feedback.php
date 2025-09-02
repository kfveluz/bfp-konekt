<?php
session_start();
require_once(__DIR__ . '/../config/db_connect.php');
header('Content-Type: application/json');

// Restrict to logged-in admin or BFP users only
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['admin', 'bfp'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Only admin and BFP users can submit feedback.']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $incidentId = trim($data['incident_id'] ?? '');
    $rating = isset($data['rating']) ? intval($data['rating']) : 0;
    $feedbackText = trim($data['feedback_text'] ?? '');
    $adminResponse = trim($data['admin_response'] ?? '');
    $evidenceImage = trim($data['evidence_image'] ?? '');
    
    // Optional detailed ratings
    $responseTimeRating = isset($data['response_time_rating']) ? intval($data['response_time_rating']) : null;
    $professionalismRating = isset($data['professionalism_rating']) ? intval($data['professionalism_rating']) : null;
    $effectivenessRating = isset($data['effectiveness_rating']) ? intval($data['effectiveness_rating']) : null;
    $overallSatisfaction = isset($data['overall_satisfaction']) ? intval($data['overall_satisfaction']) : null;
    $category = trim($data['category'] ?? 'general');
    
    // Enforce allowed categories
    $allowedCategories = ['system_performance', 'bfp_resolve_team'];
    if (!in_array($category, $allowedCategories)) {
        echo json_encode(['success' => false, 'message' => 'Invalid category. Allowed: system_performance, bfp_resolve_team']);
        exit;
    }
    
    // Validation
    if (!$incidentId || !$rating) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields: incident_id and rating are required']);
        exit;
    }
    
    if ($rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
        exit;
    }
    
    // Validate optional ratings
    $optionalRatings = [$responseTimeRating, $professionalismRating, $effectivenessRating, $overallSatisfaction];
    foreach ($optionalRatings as $optRating) {
        if ($optRating !== null && ($optRating < 1 || $optRating > 5)) {
            echo json_encode(['success' => false, 'message' => 'All ratings must be between 1 and 5']);
            exit;
        }
    }
    
    $conn = getDBConnection();
    
    // Check if feedback already exists for this incident and user
    $checkStmt = $conn->prepare("SELECT id FROM incident_feedback WHERE incident_id = ? AND user_id = ?");
    $checkStmt->bind_param('ss', $incidentId, $_SESSION['user_id']);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Feedback already submitted for this incident']);
        exit;
    }
    $checkStmt->close();
    
    // Insert feedback
    $stmt = $conn->prepare("INSERT INTO incident_feedback (incident_id, user_id, rating, response_time_rating, professionalism_rating, effectiveness_rating, overall_satisfaction, feedback_text, admin_response, evidence_image, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssiiiiissss', $incidentId, $_SESSION['user_id'], $rating, $responseTimeRating, $professionalismRating, $effectivenessRating, $overallSatisfaction, $feedbackText, $adminResponse, $evidenceImage, $category);
    
    if ($stmt->execute()) {
        $feedbackId = $conn->insert_id;
        
        // If there's an evidence image, update the feedback_images table to link it
        if (!empty($evidenceImage)) {
            $updateImageStmt = $conn->prepare("UPDATE feedback_images SET feedback_id = ? WHERE filepath = ? AND uploaded_by = ?");
            $updateImageStmt->bind_param('iss', $feedbackId, $evidenceImage, $_SESSION['user_id']);
            $updateImageStmt->execute();
            $updateImageStmt->close();
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Feedback submitted successfully. Thank you for your response!',
            'feedback_id' => $feedbackId
        ]);
    } else {
        throw new Exception('Failed to save feedback: ' . $stmt->error);
    }
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Feedback submission error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error. Please try again later.']);
}
?> 