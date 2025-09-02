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

try {
    // Get all incidents from the database
    $query = "SELECT 
        i.id,
        i.user_id,
        u.name as user_name,
        i.description,
        i.location,
        i.type as severity,
        i.created_at as timestamp,
        i.source as source_type,
        i.url as source_id,
        i.source as source_name,
        i.status
    FROM incidents i
    LEFT JOIN users u ON i.user_id = u.id
    WHERE i.fetched_by_apify = 1 AND i.status NOT IN ('resolved', 'false_alarm', 'non_incident')
    ORDER BY i.created_at DESC
    LIMIT 100";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $incidents = $stmt->fetchAll();

    // Format the incidents to match the frontend structure
    $formattedIncidents = array_map(function($incident) {
        return [
            'id' => $incident['id'],
            'user_id' => $incident['user_id'],
            'user_name' => $incident['user_name'],
            'message' => $incident['description'],
            'location' => $incident['location'],
            'severity' => $incident['severity'],
            'timestamp' => $incident['timestamp'],
            'source' => [
                'type' => $incident['source_type'],
                'pageId' => $incident['source_id'],
                'pageName' => $incident['source_name']
            ],
            'status' => $incident['status']
        ];
    }, $incidents);

    echo json_encode($formattedIncidents);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 