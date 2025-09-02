<?php
require_once(__DIR__ . '/../config/db_connect.php');
header('Content-Type: application/json');

try {
    $conn = getDBConnection();
    
    // Check if incident_evidence table exists
    $tableExists = $conn->query("SHOW TABLES LIKE 'incident_evidence'");
    if ($tableExists->num_rows == 0) {
        echo json_encode([
            'success' => true,
            'stats' => [
                'total_evidence' => 0,
                'total_incidents_with_evidence' => 0,
                'total_users_uploaded' => 0,
                'recent_uploads_24h' => 0,
                'recent_uploads_7d' => 0
            ],
            'message' => 'Evidence table does not exist yet'
        ]);
        exit;
    }

    // Get evidence statistics
    $statsQuery = "SELECT 
        COUNT(*) as total_evidence,
        COUNT(DISTINCT incident_id) as total_incidents_with_evidence,
        COUNT(DISTINCT user_id) as total_users_uploaded,
        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as recent_uploads_24h,
        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as recent_uploads_7d
    FROM incident_evidence";
    
    $statsStmt = $conn->prepare($statsQuery);
    $statsStmt->execute();
    $statsResult = $statsStmt->get_result();
    $stats = $statsResult->fetch_assoc();
    
    // Get recent evidence uploads
    $recentQuery = "SELECT 
        e.*, 
        u.name as user_name,
        i.description as incident_message,
        i.location as incident_location
    FROM incident_evidence e
    LEFT JOIN users u ON e.user_id = u.id
    LEFT JOIN incidents i ON e.incident_id = i.id
    ORDER BY e.created_at DESC
    LIMIT 10";
    
    $recentStmt = $conn->prepare($recentQuery);
    $recentStmt->execute();
    $recentResult = $recentStmt->get_result();
    $recentEvidence = [];
    while ($row = $recentResult->fetch_assoc()) {
        $recentEvidence[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_evidence' => intval($stats['total_evidence']),
            'total_incidents_with_evidence' => intval($stats['total_incidents_with_evidence']),
            'total_users_uploaded' => intval($stats['total_users_uploaded']),
            'recent_uploads_24h' => intval($stats['recent_uploads_24h']),
            'recent_uploads_7d' => intval($stats['recent_uploads_7d'])
        ],
        'recent_evidence' => $recentEvidence
    ]);
    
} catch (Exception $e) {
    error_log('Get evidence stats error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
}
?> 