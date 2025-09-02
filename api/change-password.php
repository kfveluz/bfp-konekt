<?php
header('Content-Type: application/json');
require_once '../config/db_connect.php';

$response = ['success' => false, 'message' => ''];

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['userId']) || !isset($data['newPassword'])) {
        throw new Exception('Missing required fields');
    }

    $conn = getDBConnection();
    $userId = $conn->real_escape_string($data['userId']);
    $newPassword = password_hash($data['newPassword'], PASSWORD_DEFAULT);

    $sql = "UPDATE users SET password = ?, is_default_password = 0 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $newPassword, $userId);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Password changed successfully';
    } else {
        throw new Exception('Failed to update password: ' . $stmt->error);
    }

    closeDBConnection($conn);
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?> 