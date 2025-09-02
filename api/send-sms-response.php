<?php
require_once(__DIR__ . '/../config/db_connect.php');
header('Content-Type: application/json');

// This endpoint is for BFP staff to send SMS updates to users
// It should be protected with admin authentication

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $incidentId = $data['incident_id'] ?? '';
    $status = $data['status'] ?? '';
    $message = $data['message'] ?? '';
    
    if (!$incidentId || !$status || !$message) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields: incident_id, status, message']);
        exit;
    }
    
    $conn = getDBConnection();
    
    // Get incident details including phone and SMS preferences
    $stmt = $conn->prepare("SELECT phone, custom_id, type, sms_response FROM incidents WHERE custom_id = ?");
    $stmt->bind_param('s', $incidentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Incident not found']);
        exit;
    }
    
    $incident = $result->fetch_assoc();
    
    // Check if user opted for SMS response updates
    if (!$incident['sms_response']) {
        echo json_encode(['success' => false, 'message' => 'User has not opted for SMS response updates']);
        exit;
    }
    
    // Send FREE SMS update
    $smsSent = sendFreeSMSResponse($incident['phone'], $incident['custom_id'], $status, $message);
    
    if ($smsSent) {
        // Update incident status in database
        $updateStmt = $conn->prepare("UPDATE incidents SET status = ? WHERE custom_id = ?");
        $updateStmt->bind_param('ss', $status, $incidentId);
        $updateStmt->execute();
        
        echo json_encode([
            'success' => true, 
            'message' => 'FREE SMS response sent successfully',
            'incident_id' => $incidentId,
            'phone' => $incident['phone'],
            'sms_method' => 'FREE Email-to-SMS'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send FREE SMS response']);
    }
    
} catch (Exception $e) {
    error_log("SMS response error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error. Please try again.']);
}

// FREE SMS response function using email-to-SMS gateways
function sendFreeSMSResponse($phone, $customId, $status, $message) {
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
    
    // Status emojis for better user experience
    $statusEmoji = [
        'responding' => '🚒',
        'on_scene' => '📍',
        'resolved' => '✅',
        'false_alarm' => '⚠️',
        'cancelled' => '❌'
    ];
    
    $emoji = $statusEmoji[$status] ?? '📱';
    $smsMessage = "BFP Konekt Update (ID: $customId): $emoji $message";
    
    // Email headers
    $subject = "BFP Konekt Status Update";
    $headers = [
        'From: BFP Konekt <noreply@bfpkonekt.com>',
        'Reply-To: noreply@bfpkonekt.com',
        'Content-Type: text/plain; charset=UTF-8'
    ];
    
    // Send email (which gets delivered as SMS)
    $sent = mail($email, $subject, $smsMessage, implode("\r\n", $headers));
    
    if ($sent) {
        error_log("FREE SMS Response sent to $phone via $carrier email gateway: $email");
        return true;
    } else {
        error_log("Failed to send FREE SMS Response to $phone via $carrier email gateway: $email");
        return false;
    }
}
?> 