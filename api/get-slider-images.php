<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/../config/db_connect.php');

header('Content-Type: application/json');

try {
    $conn = getDBConnection();
    $sql = 'SELECT id, url FROM slider_images ORDER BY display_order ASC';
    $result = $conn->query($sql);
    $images = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $images[] = $row;
        }
    }
    echo json_encode([
        'success' => true,
        'images' => $images
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch slider images: ' . $e->getMessage()
    ]);
}
?> 