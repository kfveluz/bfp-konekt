<?php
require_once __DIR__ . '/../config/db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit();
}

$conn = getDBConnection();

// Save Apify query
if (isset($data['apify_query'])) {
    $apifyQuery = trim($data['apify_query']);
    $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('apify_query', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $stmt->bind_param('s', $apifyQuery);
    $stmt->execute();
    $stmt->close();
}

// Save NLP locations (overwrite all)
if (isset($data['locations']) && is_array($data['locations'])) {
    $conn->query("TRUNCATE TABLE nlp_locations");
    $stmt = $conn->prepare("INSERT INTO nlp_locations (location) VALUES (?)");
    foreach ($data['locations'] as $loc) {
        $loc = trim($loc);
        if ($loc !== '') {
            $stmt->bind_param('s', $loc);
            $stmt->execute();
        }
    }
    $stmt->close();
}

echo json_encode(['success' => true, 'message' => 'Settings saved']); 