<?php
require_once __DIR__ . '/../config/db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['location']) || trim($data['location']) === '') {
    echo json_encode(['success' => false, 'message' => 'Location is required']);
    exit();
}
$location = trim($data['location']);
$conn = getDBConnection();
$stmt = $conn->prepare("INSERT INTO nlp_locations (location) VALUES (?)");
$stmt->bind_param('s', $location);
$stmt->execute();
$stmt->close();
echo json_encode(['success' => true, 'message' => 'Location added']); 