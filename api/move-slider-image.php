<?php
require_once __DIR__ . '/../config/db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in and is an admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$imageId = $data['imageId'] ?? null;
$direction = $data['direction'] ?? null;

if (!$imageId || !in_array($direction, ['up', 'down'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    $conn = getDBConnection();
    // Get current image's order
    $stmt = $conn->prepare('SELECT display_order FROM slider_images WHERE id = ?');
    $stmt->bind_param('i', $imageId);
    $stmt->execute();
    $result = $stmt->get_result();
    $current = $result->fetch_assoc();
    $stmt->close();
    if (!$current) {
        echo json_encode(['success' => false, 'message' => 'Image not found']);
        exit;
    }
    $currentOrder = $current['display_order'];
    $newOrder = $direction === 'up' ? $currentOrder - 1 : $currentOrder + 1;
    // Get the image to swap with
    $stmt = $conn->prepare('SELECT id FROM slider_images WHERE display_order = ?');
    $stmt->bind_param('i', $newOrder);
    $stmt->execute();
    $result = $stmt->get_result();
    $swap = $result->fetch_assoc();
    $stmt->close();
    if (!$swap) {
        echo json_encode(['success' => false, 'message' => 'Cannot move image further']);
        exit;
    }
    $conn->begin_transaction();
    $stmt = $conn->prepare('UPDATE slider_images SET display_order = ? WHERE id = ?');
    $stmt->bind_param('ii', $newOrder, $imageId);
    $stmt->execute();
    $stmt->bind_param('ii', $currentOrder, $swap['id']);
    $stmt->execute();
    $stmt->close();
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Image moved successfully']);
} catch (Exception $e) {
    if (isset($conn) && $conn->errno) {
        $conn->rollback();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Error moving image: ' . $e->getMessage()
    ]);
} 