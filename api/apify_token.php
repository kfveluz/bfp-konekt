<?php
header('Content-Type: application/json');
session_start();
require_once '../config/db_connect.php';

$response = ['success' => false, 'message' => '', 'token' => null];

try {
    $conn = getDBConnection();

    // Check if user is logged in (required for security)
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized access - Please log in');
    }

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        // Retrieve the Apify token
        $sql = "SELECT setting_value FROM settings WHERE setting_key = 'apify_token'";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $response['success'] = true;
            $response['token'] = $row['setting_value'];
            $response['message'] = 'Token retrieved successfully';
        } else {
            $response['success'] = true;
            $response['message'] = 'No token found';
        }

    } elseif ($method === 'POST') {
        // Handle POST requests (save/delete)
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['action'])) {
            throw new Exception('Missing action parameter');
        }

        if ($data['action'] === 'save') {
            // Save/update the Apify token
            if (!isset($data['token']) || empty($data['token'])) {
                throw new Exception('Missing or empty token');
            }

            $token = trim($data['token']);
            
            // Validate token format (basic validation)
            if (strlen($token) < 10) {
                throw new Exception('Token appears to be too short');
            }

            $sql = "INSERT INTO settings (setting_key, setting_value) VALUES ('apify_token', ?)
                    ON DUPLICATE KEY UPDATE setting_value = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $token, $token);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Apify token saved successfully';
                $response['token'] = $token;
            } else {
                throw new Exception('Failed to save token: ' . $stmt->error);
            }

        } elseif ($data['action'] === 'delete') {
            // Delete the Apify token
            $sql = "DELETE FROM settings WHERE setting_key = 'apify_token'";
            $result = $conn->query($sql);

            if ($result) {
                $response['success'] = true;
                $response['message'] = 'Apify token deleted successfully';
            } else {
                throw new Exception('Failed to delete token: ' . $conn->error);
            }

        } else {
            throw new Exception('Invalid action: ' . $data['action']);
        }

    } else {
        throw new Exception('Invalid request method');
    }

    closeDBConnection($conn);

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    // Log error for debugging
    error_log('Apify Token API error: ' . $e->getMessage());
}

echo json_encode($response);
?>
