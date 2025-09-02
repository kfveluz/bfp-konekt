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

if (!$imageId) {
    echo json_encode(['success' => false, 'message' => 'Image ID is required']);
    exit;
}

try {
    $conn = getDBConnection();
    // Get the image URL before deleting
    $stmt = $conn->prepare('SELECT url FROM slider_images WHERE id = ?');
    $stmt->bind_param('i', $imageId);
    $stmt->execute();
    $result = $stmt->get_result();
    $image = $result->fetch_assoc();
    $stmt->close();
    if (!$image) {
        echo json_encode(['success' => false, 'message' => 'Image not found']);
        exit;
    }
    // Delete from database
    $stmt = $conn->prepare('DELETE FROM slider_images WHERE id = ?');
    $stmt->bind_param('i', $imageId);
    $stmt->execute();
    $stmt->close();
    // Delete the file
    $filePath = __DIR__ . '/../' . $image['url'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    // Reorder remaining images
    $result = $conn->query('SELECT id FROM slider_images ORDER BY display_order ASC');
    $images = [];
    while ($row = $result->fetch_assoc()) {
        $images[] = $row['id'];
    }
    $order = 1;
    foreach ($images as $id) {
        $stmt2 = $conn->prepare('UPDATE slider_images SET display_order = ? WHERE id = ?');
        $stmt2->bind_param('ii', $order, $id);
        $stmt2->execute();
        $stmt2->close();
        $order++;
    }
    echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting image: ' . $e->getMessage()
    ]);
} 