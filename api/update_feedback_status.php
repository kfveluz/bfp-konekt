<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $feedbackId = $data['feedback_id'] ?? null;
    $status = $data['status'] ?? null;
    $adminResponse = $data['admin_response'] ?? null;
    
    if (!$feedbackId || !$status) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing feedback_id or status']);
        exit();
    }
    
    // Validate status
    $allowedStatuses = ['pending', 'reviewed', 'addressed', 'closed'];
    if (!in_array($status, $allowedStatuses)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid status provided']);
        exit();
    }
    
    // Update feedback status
    $query = "UPDATE incident_feedback SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':feedback_id', $feedbackId);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // If admin response is provided, send SMS to user
        if ($adminResponse && $status === 'addressed') {
            // Get feedback details to send SMS
            $feedbackQuery = "SELECT phone, incident_id FROM incident_feedback WHERE id = ?";
            $feedbackStmt = $conn->prepare($feedbackQuery);
            $feedbackStmt->bindParam(':feedback_id', $feedbackId);
            $feedbackStmt->execute();
            $feedbackData = $feedbackStmt->fetch();
            
            if ($feedbackData) {
                $smsSent = sendFeedbackResponseSMS($feedbackData['phone'], $feedbackData['incident_id'], $adminResponse);
            }
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Feedback status updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'error' => 'Feedback not found or status already the same'
        ]);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}

// Send SMS response to user about their feedback
function sendFeedbackResponseSMS($phone, $incidentId, $adminResponse) {
    // Philippine carrier email-to-SMS gateways
    $carriers = [
        'globe' => [
            'prefixes' => ['905', '906', '915', '916', '917', '926', '927', '935', '936', '937'],
            'email' => '@globe.com.ph'
        ],
        'smart' => [
            'prefixes' => ['907', '912', '913', '914', '918', '919', '920', '921', '928', '929', '930', '931', '938', '939'],
            'email' => '@smart.com.ph'
        ],
        'sun' => [
            'prefixes' => ['922', '923', '925', '932', '933', '934'],
            'email' => '@sun.com.ph'
        ]
    ];
    
    // Detect carrier based on phone number prefix
    $prefix = substr($phone, 0, 3);
    $carrier = null;
    
    foreach ($carriers as $carrierName => $data) {
        if (in_array($prefix, $data['prefixes'])) {
            $carrier = $carrierName;
            break;
        }
    }
    
    if (!$carrier) {
        error_log("Unknown carrier for phone: $phone");
        return false;
    }
    
    // Format email address for carrier's SMS gateway
    $email = $phone . $carriers[$carrier]['email'];
    
    // Create SMS message (truncate if too long)
    $responseText = strlen($adminResponse) > 100 ? substr($adminResponse, 0, 97) . '...' : $adminResponse;
    $smsMessage = "BFP Konekt Response (Incident $incidentId): $responseText";
    
    // Email headers
    $subject = "BFP Konekt Feedback Response";
    $headers = [
        'From: BFP Konekt <noreply@bfpkonekt.com>',
        'Reply-To: noreply@bfpkonekt.com',
        'Content-Type: text/plain; charset=UTF-8'
    ];
    
    // Send email (which gets delivered as SMS)
    $sent = mail($email, $subject, $smsMessage, implode("\r\n", $headers));
    
    if ($sent) {
        error_log("Feedback response SMS sent to $phone via $carrier email gateway: $email");
        return true;
    } else {
        error_log("Failed to send feedback response SMS to $phone via $carrier email gateway: $email");
        return false;
    }
}
?> 