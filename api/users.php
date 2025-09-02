<?php
header('Content-Type: application/json');
session_start();
require_once '../config/db_connect.php';

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/php_errors.log');

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    $conn = getDBConnection();
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Log the request method and data
    error_log("Request Method: " . $method);
    if ($method === 'POST') {
        $rawData = file_get_contents('php://input');
        error_log("Raw POST data: " . $rawData);
    }

    if ($method === 'GET') {
        // List all users
        $result = $conn->query("SELECT id, name, type FROM users ORDER BY id ASC");
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $response['success'] = true;
        $response['data'] = $users;
    } elseif ($method === 'POST') {
        // RBAC: Only allow admins
        if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['name'], $data['type'], $data['password'], $data['email'])) {
            throw new Exception('Missing required fields');
        }
        $created_by = $_SESSION['user_id'] ?? null;
        $created_at = date('Y-m-d H:i:s');
        error_log("Decoded POST data: " . print_r($data, true));
        
        // Validate user type
        if (!in_array($data['type'], ['admin', 'user'])) {
            throw new Exception('Invalid user type');
        }

        // Validate password
        if (strlen($data['password']) < 8) {
            throw new Exception('Password must be at least 8 characters long');
        }

        // Generate user ID
        $prefix = $data['type'] === 'admin' ? 'BFPK' : 'BFPU';
        
        // Get the highest existing ID for the given type
        $sql = "SELECT MAX(CAST(SUBSTRING(id, 5) AS UNSIGNED)) as max_num FROM users WHERE id LIKE ?";
        $stmt = $conn->prepare($sql);
        $prefixPattern = $prefix . '%';
        $stmt->bind_param("s", $prefixPattern);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        error_log("Current max number for prefix $prefix: " . ($row['max_num'] ?? 'none'));
        
        // Get the next number, defaulting to 1 if no existing IDs
        $nextNum = ($row['max_num'] ?? 0) + 1;
        
        // Generate new ID with proper padding
        $newId = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        error_log("Generated new ID: " . $newId);
        
        // Hash the password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Insert the new user
        $sql = "INSERT INTO users (id, name, email, type, password, is_default_password, created_by, created_at) VALUES (?, ?, ?, ?, ?, 1, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $newId, $data['name'], $data['email'], $data['type'], $hashedPassword, $created_by, $created_at);
        
        if ($stmt->execute()) {
            // Log user creation
            $logStmt = $conn->prepare("INSERT INTO user_logs (user_id, action) VALUES (?, ?)");
            $logAction = 'created user ' . $newId;
            $logStmt->bind_param('ss', $created_by, $logAction);
            $logStmt->execute();
            $logStmt->close();
            $response['success'] = true;
            $response['message'] = 'User created successfully';
            $response['data'] = [
                'id' => $newId,
                'name' => $data['name'],
                'type' => $data['type']
            ];
            error_log("User created successfully: " . $newId);
        } else {
            throw new Exception('Failed to create user: ' . $stmt->error);
        }
    } elseif ($method === 'DELETE') {
        // Delete a user
        $data = json_decode(file_get_contents('php://input'), true);
        error_log("DELETE request data: " . print_r($data, true));
        
        if (!isset($data['id'])) {
            throw new Exception('Missing user ID');
        }

        // Prevent deleting the last admin
        if (strpos($data['id'], 'BFPK') === 0) {
            $adminCount = $conn->query("SELECT COUNT(*) as count FROM users WHERE type = 'admin'")->fetch_assoc()['count'];
            error_log("Current admin count: " . $adminCount);
            if ($adminCount <= 1) {
                throw new Exception('Cannot delete the last admin user');
            }
        }

        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("s", $data['id']);
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'User deleted successfully';
            error_log("User deleted successfully: " . $data['id']);
        } else {
            throw new Exception('Failed to delete user: ' . $stmt->error);
        }
    } else {
        throw new Exception('Invalid request method');
    }
    closeDBConnection($conn);
} catch (Exception $e) {
    error_log("Error in users.php: " . $e->getMessage());
    $response['message'] = $e->getMessage();
    if (isset($conn)) {
        closeDBConnection($conn);
    }
}
echo json_encode($response);
?> 