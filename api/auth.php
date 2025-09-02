<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
session_start();
require_once '../config/db_connect.php';

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['userId']) || !isset($data['password'])) {
            throw new Exception('Missing required fields');
        }
        
        $conn = getDBConnection();
        
        $userId = $conn->real_escape_string($data['userId']);
        $password = $data['password'];
        
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception('Database prepare error: ' . $conn->error);
        }
        
        $stmt->bind_param("s", $userId);
        
        if (!$stmt->execute()) {
            throw new Exception('Database execute error: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Set PHP session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_type'] = $user['type'];
                $response['success'] = true;
                $response['message'] = 'Login successful';
                $response['data'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'type' => $user['type'],
                    'isDefaultPassword' => (bool)$user['is_default_password']
                ];
                require_once __DIR__ . '/utils.php';
                log_user_activity($_SESSION['user_id'], 'Logged In');
            } else {
                throw new Exception('Invalid password');
            }
        } else {
            throw new Exception('User not found');
        }
        
        closeDBConnection($conn);
    } else {
        throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Login error: ' . $e->getMessage());
}

echo json_encode($response);
?> 