<?php
session_start();
require_once(__DIR__ . '/../config/db_connect.php');
require_once(__DIR__ . '/utils.php'); // Added for log_user_activity
header('Content-Type: application/json');

// Allow any logged-in user
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Please log in first.']);
    exit;
}

try {
    if (!isset($_POST['incident_id']) || !isset($_POST['comment']) || !isset($_FILES['image'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
        exit;
    }
    $incidentId = trim($_POST['incident_id']);
    $user_id = $_SESSION['user_id'] ?? 'system';
    $comment = trim($_POST['comment']);
    $file = $_FILES['image'];

    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF images are allowed.']);
        exit;
    }
    // Validate file size (max 5MB)
    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'File size too large. Maximum size is 5MB.']);
        exit;
    }
    // Create upload directory if it doesn't exist
    $uploadDir = __DIR__ . '/../uploads/evidence/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    // Generate unique filename
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'evidence_' . time() . '_' . $user_id . '.' . $fileExtension;
    $filepath = $uploadDir . $filename;
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to move uploaded file');
    }
    $imagePath = 'uploads/evidence/' . $filename;
    // Save to database
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO incident_evidence (incident_id, user_id, image_path, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $incidentId, $user_id, $imagePath, $comment);
    if ($stmt->execute()) {
        $evidenceId = $conn->insert_id; // Get the ID of the newly inserted evidence
        log_user_activity($user_id, 'Uploaded Evidence', 'Incident ID: ' . $incidentId . ', Evidence ID: ' . $evidenceId);
        echo json_encode(['success' => true, 'message' => 'Evidence uploaded successfully', 'image_path' => $imagePath]);
    } else {
        throw new Exception('Failed to save evidence: ' . $stmt->error);
    }
    $stmt->close();
} catch (Exception $e) {
    error_log('Evidence upload error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
}
?> 