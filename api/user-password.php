<?php
header('Content-Type: application/json');
session_start();
require_once '../config/db_connect.php';

$response = ['success' => false, 'message' => ''];

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['userId']) || !isset($data['newPassword'])) {
        throw new Exception('Missing required fields');
    }

    // Validate password length
    if (strlen($data['newPassword']) < 8) {
        throw new Exception('Password must be at least 8 characters long');
    }

    // Validate password complexity
    if (!preg_match('/[A-Z]/', $data['newPassword']) || // At least one uppercase letter
        !preg_match('/[a-z]/', $data['newPassword']) || // At least one lowercase letter
        !preg_match('/[0-9]/', $data['newPassword']) || // At least one number
        !preg_match('/[^A-Za-z0-9]/', $data['newPassword'])) { // At least one special character
        throw new Exception('Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character');
    }

    $conn = getDBConnection();

    // Check if user exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $checkStmt->bind_param("s", $data['userId']);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows === 0) {
        throw new Exception('User not found');
    }

    // Hash the new password
    $newPassword = password_hash($data['newPassword'], PASSWORD_DEFAULT);

    // Update the password
    $sql = "UPDATE users SET password = ?, is_default_password = 0 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $newPassword, $data['userId']);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Password changed successfully';
    } else {
        throw new Exception('Failed to update password: ' . $stmt->error);
    }

    closeDBConnection($conn);
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    if (isset($conn)) {
        closeDBConnection($conn);
    }
}
echo json_encode($response);
?> 