<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get the POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['incident_id']) || !isset($input['status'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing incident_id or status']);
    exit();
}

$incidentId = $input['incident_id'];
$status = $input['status'];

// Validate status to ensure it's one of the allowed ENUM values
$allowedStatuses = ['active', 'resolved', 'false_alarm', 'non_incident'];
if (!in_array($status, $allowedStatuses)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid status provided']);
    exit();
}

try {
    // Update the incident status in the database
    $query = "UPDATE incidents SET status = :status WHERE id = :incident_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':incident_id', $incidentId);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Incident status updated successfully.']);
        require_once __DIR__ . '/utils.php';
        log_user_activity($_SESSION['user_id'] ?? 'system', 'Updated Incident Status', 'Incident ID: ' . $incidentId . ', New Status: ' . $status);
    } else {
        echo json_encode(['success' => false, 'error' => 'Incident not found or status already the same.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?> 