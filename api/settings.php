<?php
header('Content-Type: application/json');
session_start();
require_once '../config/db_connect.php';

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    $conn = getDBConnection();

    // Check if user is logged in (optional, but good practice for settings)
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized access');
    }

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        // Fetch all settings
        $sql = "SELECT setting_key, setting_value FROM settings";
        $result = $conn->query($sql);

        $settings = [];
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        $response['success'] = true;
        $response['data'] = $settings;

    } elseif ($method === 'POST') {
        // Update a setting
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['setting_key']) || !isset($data['setting_value'])) {
            throw new Exception('Missing setting_key or setting_value');
        }

        $settingKey = $data['setting_key'];
        $settingValue = $data['setting_value'];

        $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE setting_value = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $settingKey, $settingValue, $settingValue);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Setting updated successfully';
        } else {
            throw new Exception('Failed to update setting: ' . $stmt->error);
        }

    } else {
        throw new Exception('Invalid request method');
    }

    closeDBConnection($conn);

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    // Log error for debugging
    error_log('Settings API error: ' . $e->getMessage());
}

echo json_encode($response);
?> 