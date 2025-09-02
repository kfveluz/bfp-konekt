<?php
// Free SMS Solution using Email-to-SMS Gateways
// This works with Philippine mobile carriers

function sendFreeSMS($phone, $message) {
    // Philippine carrier email-to-SMS gateways
    $carriers = [
        'globe' => [
            'domains' => ['globe.com.ph', 'globe.com.ph'],
            'format' => '{number}@globe.com.ph'
        ],
        'smart' => [
            'domains' => ['smart.com.ph'],
            'format' => '{number}@smart.com.ph'
        ],
        'sun' => [
            'domains' => ['sun.com.ph'],
            'format' => '{number}@sun.com.ph'
        ],
        'tm' => [
            'domains' => ['tm.com.ph'],
            'format' => '{number}@tm.com.ph'
        ]
    ];
    
    // Detect carrier based on phone number prefix
    $carrier = detectCarrier($phone);
    
    if (!$carrier || !isset($carriers[$carrier])) {
        error_log("Unknown carrier for phone: $phone");
        return false;
    }
    
    // Format email address
    $email = str_replace('{number}', $phone, $carriers[$carrier]['format']);
    
    // Send email
    return sendEmailSMS($email, $message, $phone);
}

function detectCarrier($phone) {
    // Philippine mobile prefixes
    $prefixes = [
        'globe' => ['905', '906', '915', '916', '917', '926', '927', '935', '936', '937'],
        'smart' => ['907', '912', '913', '914', '918', '919', '920', '921', '928', '929', '930', '931', '938', '939'],
        'sun' => ['922', '923', '925', '932', '933', '934'],
        'tm' => ['905', '906', '915', '916', '917', '926', '927', '935', '936', '937'] // TM uses Globe network
    ];
    
    $prefix = substr($phone, 0, 3);
    
    foreach ($prefixes as $carrier => $carrierPrefixes) {
        if (in_array($prefix, $carrierPrefixes)) {
            return $carrier;
        }
    }
    
    return null;
}

function sendEmailSMS($email, $message, $phone) {
    $subject = "BFP Konekt Emergency Alert";
    $headers = [
        'From: BFP Konekt <noreply@bfpkonekt.com>',
        'Reply-To: noreply@bfpkonekt.com',
        'Content-Type: text/plain; charset=UTF-8'
    ];
    
    // Send email
    $sent = mail($email, $subject, $message, implode("\r\n", $headers));
    
    if ($sent) {
        error_log("Free SMS sent to $phone via email: $email");
        return true;
    } else {
        error_log("Failed to send free SMS to $phone via email: $email");
        return false;
    }
}

// Usage example
if (isset($_POST['phone']) && isset($_POST['message'])) {
    $phone = $_POST['phone'];
    $message = $_POST['message'];
    
    $result = sendFreeSMS($phone, $message);
    
    echo json_encode([
        'success' => $result,
        'message' => $result ? 'SMS sent successfully' : 'Failed to send SMS'
    ]);
}
?> 