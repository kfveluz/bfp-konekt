<?php
require_once(__DIR__ . '/../config/db_connect.php');
header('Content-Type: application/json');

try {
    if (!isset($_GET['incident_id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing incident_id parameter.']);
        exit;
    }
    $incidentId = trim($_GET['incident_id']);
    $conn = getDBConnection();
    $query = "SELECT 
        e.id,
        e.incident_id,
        e.user_id,
        e.image_path,
        e.comment,
        e.created_at,
        u.name as user_name
    FROM incident_evidence e
    LEFT JOIN users u ON e.user_id = u.id
    ORDER BY e.created_at DESC
    LIMIT 100";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $incidentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $evidenceRows = [];
    while ($row = $result->fetch_assoc()) {
        $evidenceRows[] = $row;
    }
    $stmt->close();
    $formattedEvidence = array_map(function($evidence) {
        return [
            'id' => $evidence['id'],
            'incident_id' => $evidence['incident_id'],
            'user_id' => $evidence['user_id'],
            'user_name' => $evidence['user_name'],
            'image_path' => $evidence['image_path'],
            'comment' => $evidence['comment'],
            'created_at' => $evidence['created_at']
        ];
    }, $evidenceRows);
    echo json_encode(['success' => true, 'evidence' => $formattedEvidence]);
} catch (Exception $e) {
    error_log('Get evidence error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
}
?> 