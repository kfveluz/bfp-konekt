<?php
// SMS Configuration for BFP Konekt
// Configure your SMS provider settings here

// SMS Provider Settings
define('SMS_ENABLED', false); // Set to true when SMS is configured
define('SMS_PROVIDER', 'twilio'); // Options: twilio, nexmo, local

// Twilio Configuration (if using Twilio)
define('TWILIO_ACCOUNT_SID', 'your_account_sid_here');
define('TWILIO_AUTH_TOKEN', 'your_auth_token_here');
define('TWILIO_PHONE_NUMBER', '+1234567890'); // Your Twilio phone number

// Nexmo Configuration (if using Nexmo/Vonage)
define('NEXMO_API_KEY', 'your_api_key_here');
define('NEXMO_API_SECRET', 'your_api_secret_here');
define('NEXMO_FROM_NUMBER', 'BFPKonekt');

// Local SMS Gateway Configuration (if using local gateway)
define('LOCAL_SMS_GATEWAY_URL', 'http://localhost/sms-gateway/send.php');
define('LOCAL_SMS_API_KEY', 'your_local_api_key');

// SMS Message Templates
define('SMS_CONFIRMATION_TEMPLATE', 'BFP Konekt: Your emergency alert (ID: {custom_id}) has been received. Type: {type}. BFP is responding. Stay safe.');
define('SMS_RESPONSE_TEMPLATE', 'BFP Konekt Update (ID: {custom_id}): {emoji} {message}');

// SMS Status Messages
$SMS_STATUS_MESSAGES = [
    'responding' => 'BFP is responding to your emergency. Fire trucks are on the way.',
    'on_scene' => 'BFP has arrived at the scene and is handling the situation.',
    'resolved' => 'The emergency has been resolved. Thank you for your report.',
    'false_alarm' => 'This was determined to be a false alarm. No further action needed.',
    'cancelled' => 'The emergency response has been cancelled. If you still need help, please call 911.'
];

// Rate limiting for SMS
define('SMS_RATE_LIMIT_PER_HOUR', 10); // Maximum SMS per hour per phone number
define('SMS_RATE_LIMIT_PER_DAY', 50);  // Maximum SMS per day per phone number

// Debug mode (logs SMS instead of sending)
define('SMS_DEBUG_MODE', true); // Set to false in production
?> 