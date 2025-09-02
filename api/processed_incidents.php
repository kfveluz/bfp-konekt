<?php
require_once '../config/db_connect.php'; // Adjust path as needed

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $url = trim($data['url'] ?? '');
    $status = trim($data['status'] ?? '');

    if ($url && in_array($status, ['resolved', 'false_alarm', 'non_incident'])) {
        $stmt = $conn->prepare("INSERT IGNORE INTO processed_incidents (url, status) VALUES (?, ?)");
        $stmt->bind_param("ss", $url, $status);
        $success = $stmt->execute();
        echo json_encode(['success' => $success]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
    }
    exit;
}

if ($method === 'GET') {
    $result = $conn->query("SELECT url FROM processed_incidents");
    $urls = [];
    while ($row = $result->fetch_assoc()) {
        $urls[] = $row['url'];
    }
    echo json_encode($urls);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']); 