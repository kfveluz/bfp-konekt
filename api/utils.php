<?php
function log_user_activity($user_id, $action, $details = null) {
    require_once __DIR__ . '/../config/db_connect.php';
    $conn = getDBConnection();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $stmt = $conn->prepare("INSERT INTO user_activity_log (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sssss', $user_id, $action, $details, $ip, $ua);
    $stmt->execute();
    $stmt->close();
} 