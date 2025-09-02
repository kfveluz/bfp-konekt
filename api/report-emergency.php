<?php
require_once(__DIR__ . '/../config/db_connect.php');
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    // Log incoming data for debugging
    error_log("Emergency report received: " . print_r($data, true));

    $phone = trim($data['phone'] ?? '');
    $type = trim($data['type'] ?? '');
    $description = trim($data['message'] ?? '');
    $lat = isset($data['lat']) ? floatval($data['lat']) : null;
    $lng = isset($data['lng']) ? floatval($data['lng']) : null;
    $ip = trim($data['ip'] ?? $_SERVER['REMOTE_ADDR']);
    $smsConfirmation = isset($data['smsConfirmation']) ? (bool)$data['smsConfirmation'] : true;
    $smsResponse = isset($data['smsResponse']) ? (bool)$data['smsResponse'] : true;
    $source = 'login';

    // Validate Philippine phone number
    if (!preg_match('/^09\d{9}$/', $phone)) {
        echo json_encode(['success' => false, 'message' => 'Invalid Philippine mobile number format. Must be 11 digits starting with 09.']);
        exit;
    }

    if (!$phone || !$type || !$description || !$lat || !$lng) {
        error_log("Emergency report validation failed: Missing required fields.");
        echo json_encode(['success' => false, 'message' => 'All fields and location are required.']);
        exit;
    }

    $conn = getDBConnection();
    
    // Ensure custom_id column exists
    $conn->query("ALTER TABLE incidents ADD COLUMN IF NOT EXISTS custom_id VARCHAR(10) UNIQUE AFTER id");
    $conn->query("ALTER TABLE incidents ADD COLUMN IF NOT EXISTS sms_confirmation BOOLEAN DEFAULT TRUE AFTER source");
    $conn->query("ALTER TABLE incidents ADD COLUMN IF NOT EXISTS sms_response BOOLEAN DEFAULT TRUE AFTER sms_confirmation");

    // Create table if not exists, with new columns
    $sql = "CREATE TABLE IF NOT EXISTS incidents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        custom_id VARCHAR(10) UNIQUE,
        phone VARCHAR(32),
        type VARCHAR(64),
        description TEXT,
        lat DOUBLE,
        lng DOUBLE,
        ip VARCHAR(64),
        status VARCHAR(32) DEFAULT 'active',
        source VARCHAR(32) DEFAULT 'unknown',
        sms_confirmation BOOLEAN DEFAULT TRUE,
        sms_response BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($sql);

    // Rate limiting: allow only 3 entries per phone number in the last 10 minutes
    $rateStmt = $conn->prepare("SELECT COUNT(*) FROM incidents WHERE phone = ? AND created_at > (NOW() - INTERVAL 10 MINUTE)");
    $rateStmt->bind_param('s', $phone);
    $rateStmt->execute();
    $rateStmt->bind_result($recentCount);
    $rateStmt->fetch();
    $rateStmt->close();
    
    if ($recentCount >= 3) {
        error_log("Emergency report rate limit hit for phone: " . $phone);
        echo json_encode(['success' => false, 'message' => 'You can only send 3 emergency alerts per 10 minutes from the same phone number. Please wait before submitting again.']);
        exit;
    }

    // Generate next custom_id (EA0001, EA0002, ...)
    $result = $conn->query("SELECT custom_id FROM incidents WHERE custom_id IS NOT NULL ORDER BY id DESC LIMIT 1");
    if ($row = $result->fetch_assoc()) {
        $lastId = $row['custom_id'];
        $num = intval(substr($lastId, 2));
        $nextNum = $num + 1;
    } else {
        $nextNum = 1;
    }
    $customId = 'EA' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

    // Insert with SMS preferences
    $stmt = $conn->prepare("INSERT INTO incidents (custom_id, phone, type, description, lat, lng, ip, status, source, sms_confirmation, sms_response) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?)");
    $stmt->bind_param('ssssddssii', $customId, $phone, $type, $description, $lat, $lng, $ip, $source, $smsConfirmation, $smsResponse);
    
    if ($stmt->execute()) {
        // Send FREE SMS confirmation if requested
        $smsSent = false;
        if ($smsConfirmation) {
            $smsSent = sendFreeSMS($phone, $customId, $type);
        }
        
        echo json_encode([
            'success' => true, 
            'custom_id' => $customId,
            'message' => 'Emergency alert sent successfully. BFP has been notified.',
            'sms_confirmation_sent' => $smsSent,
            'sms_response_enabled' => $smsResponse,
            'sms_method' => 'FREE Email-to-SMS'
        ]);
    } else {
        error_log("Emergency report database insert failed: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Failed to save emergency report. Please try again.']);
    }
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Emergency report script error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error. Please try again later.']);
}

// FREE SMS function using email-to-SMS gateways
function sendFreeSMS($phone, $customId, $type) {
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
    
    // Create SMS message
    $smsMessage = "BFP Konekt: Your emergency alert (ID: $customId) has been received. Type: $type. BFP is responding. Stay safe.";
    
    // Email headers
    $subject = "BFP Konekt Emergency Alert";
    $headers = [
        'From: BFP Konekt <noreply@bfpkonekt.com>',
        'Reply-To: noreply@bfpkonekt.com',
        'Content-Type: text/plain; charset=UTF-8'
    ];
    
    // Send email (which gets delivered as SMS)
    $sent = mail($email, $subject, $smsMessage, implode("\r\n", $headers));
    
    if ($sent) {
        error_log("FREE SMS sent to $phone via $carrier email gateway: $email");
        return true;
    } else {
        error_log("Failed to send FREE SMS to $phone via $carrier email gateway: $email");
        return false;
    }
}
?> 