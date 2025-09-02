<?php
header('Content-Type: application/json');
require_once '../config/db_connect.php';

// Enable error logging
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit();
}

// Log incoming data for debugging
error_log("Insert incident request received: " . print_r($data, true));

// Assign variables from data *first*
$id = $data['id'] ?? '';
$type = $data['type'] ?? 'fire';
$description = $data['message'] ?? '';
$timestamp = isset($data['timestamp']) ? date('Y-m-d H:i:s', is_numeric($data['timestamp']) ? $data['timestamp']/1000 : strtotime($data['timestamp'])) : date('Y-m-d H:i:s');
$confidence = $data['confidence'] ?? 0;
$status = $data['status'] ?? 'active'; // Status from Apify data
$location = $data['location'] ?? '';
$source = $data['source'] ?? '';
$url = $data['url'] ?? '';

// Validate required fields
if (empty($id)) {
    error_log("Insert incident failed: Empty ID");
    echo json_encode(['success' => false, 'message' => 'Incident ID is required']);
    exit();
}

// Fix: If URL is empty, set to NULL to avoid duplicate '' for unique_url
if (empty($url)) {
    $url = null;
}

try {
    $conn = getDBConnection();
} catch (Exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Check current status in the database by ID first
$currentStatus = null;
$existingIncident = null;

if (!empty($id)) {
    $checkStmt = $conn->prepare("SELECT status, url FROM incidents WHERE id = ? LIMIT 1");
    $checkStmt->bind_param('s', $id);
    $checkStmt->execute();
    $checkStmt->bind_result($dbStatus, $dbUrl);
    if ($checkStmt->fetch()) {
        $currentStatus = $dbStatus;
        $existingIncident = ['id' => $id, 'status' => $dbStatus, 'url' => $dbUrl];
    }
    $checkStmt->close();
}

// If not found by ID, check by URL (permalink) - this is the key check for resolved incidents
if (!$currentStatus && !empty($url)) {
    $checkStmt = $conn->prepare("SELECT id, status FROM incidents WHERE url = ? LIMIT 1");
    $checkStmt->bind_param('s', $url);
    $checkStmt->execute();
    $checkStmt->bind_result($dbId, $dbStatus);
    if ($checkStmt->fetch()) {
        $currentStatus = $dbStatus;
        $existingIncident = ['id' => $dbId, 'status' => $dbStatus, 'url' => $url];
    }
    $checkStmt->close();
}

// If incident exists and is no longer active, do not allow scraper to overwrite its status
if ($currentStatus && $currentStatus !== 'active') {
    // Only allow manual/user updates away from a resolved/categorized state
    // If the new status is 'active' (from the scraper), ignore it and return an error
    if ($status === 'active') {
        echo json_encode([
            'success' => false, 
            'message' => "Incident already closed as '{$currentStatus}'. URL: {$url}. Scraper update ignored.",
            'existing_incident' => $existingIncident
        ]);
        closeDBConnection($conn);
        exit();
    }
}

// For new incidents or user-driven status updates, proceed with insert/update
try {
    $user_id = $_SESSION['user_id'] ?? 'system';
    $fetched_by_apify = 0;
    if ((isset($data['source']) && strtolower($data['source']) === 'apify') || isset($data['fetched_by_apify'])) {
        $fetched_by_apify = 1;
    }
    $stmt = $conn->prepare("INSERT INTO incidents (id, user_id, type, description, timestamp, confidence, status, location, source, url, fetched_by_apify)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            user_id = VALUES(user_id),
            description = VALUES(description),
            timestamp = VALUES(timestamp),
            confidence = VALUES(confidence),
            status = VALUES(status),
            location = VALUES(location),
            source = VALUES(source),
            url = VALUES(url),
            fetched_by_apify = VALUES(fetched_by_apify)");

    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }

    $bindResult = $stmt->bind_param('ssssisssssi', $id, $user_id, $type, $description, $timestamp, $confidence, $status, $location, $source, $url, $fetched_by_apify);
    
    if (!$bindResult) {
        throw new Exception("Bind parameters failed: " . $stmt->error);
    }

    $success = $stmt->execute();

    if ($success) {
        require_once __DIR__ . '/utils.php';
        log_user_activity($_SESSION['user_id'] ?? 'system', 'Created Incident', 'Incident ID: ' . $id);
        $affectedRows = $stmt->affected_rows;
        error_log("Incident insert/update successful. Affected rows: $affectedRows. ID: $id");
        echo json_encode([
            'success' => true, 
            'message' => 'Incident inserted/updated',
            'affected_rows' => $affectedRows,
            'incident_id' => $id
        ]);
    } else {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $stmt->close();
    
} catch (Exception $e) {
    error_log("Incident insert/update failed: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Insert failed: ' . $e->getMessage(),
        'incident_id' => $id
    ]);
} finally {
    closeDBConnection($conn);
} 