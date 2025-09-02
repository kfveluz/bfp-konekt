<?php
session_start();
require_once '../config/db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $conn = getDBConnection();
    
    // Check if incident_feedback table exists
    $tableExists = $conn->query("SHOW TABLES LIKE 'incident_feedback'");
    if ($tableExists->num_rows == 0) {
        echo json_encode([
            'success' => true,
            'feedback' => [],
            'stats' => [
                'total_feedback' => 0,
                'avg_rating' => 0,
                'avg_response_time' => null,
                'avg_professionalism' => null,
                'avg_effectiveness' => null,
                'avg_satisfaction' => null,
                'positive_feedback' => 0,
                'negative_feedback' => 0
            ],
            'message' => 'Feedback table does not exist yet'
        ]);
        exit;
}

try {
    // Get query parameters
    $incidentId = $_GET['incident_id'] ?? null;
    $status = $_GET['status'] ?? null;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    
    // First, let's check what columns actually exist in the incident_feedback table
    $columnsResult = $conn->query("SHOW COLUMNS FROM incident_feedback");
    $existingColumns = [];
    while ($row = $columnsResult->fetch_assoc()) {
        $existingColumns[] = $row['Field'];
    }
    
    // Build query based on existing columns
    $selectFields = ["f.id", "f.incident_id"];
    
    // Add user_id field (check if it exists)
    if (in_array('user_id', $existingColumns)) {
        $selectFields[] = "f.user_id";
    } elseif (in_array('user', $existingColumns)) {
        $selectFields[] = "f.user as user_id";
    }
    
    // Add other fields if they exist
    if (in_array('rating', $existingColumns)) $selectFields[] = "f.rating";
    if (in_array('response_time_rating', $existingColumns)) $selectFields[] = "f.response_time_rating";
    if (in_array('professionalism_rating', $existingColumns)) $selectFields[] = "f.professionalism_rating";
    if (in_array('effectiveness_rating', $existingColumns)) $selectFields[] = "f.effectiveness_rating";
    if (in_array('overall_satisfaction', $existingColumns)) $selectFields[] = "f.overall_satisfaction";
    if (in_array('feedback_text', $existingColumns)) $selectFields[] = "f.feedback_text";
    if (in_array('admin_response', $existingColumns)) $selectFields[] = "f.admin_response";
    if (in_array('evidence_image', $existingColumns)) $selectFields[] = "f.evidence_image";
    if (in_array('category', $existingColumns)) $selectFields[] = "f.category";
    if (in_array('status', $existingColumns)) $selectFields[] = "f.status";
    if (in_array('created_at', $existingColumns)) $selectFields[] = "f.created_at";
    
    // Add incident and user info
    $selectFields[] = "i.type as incident_type";
    $selectFields[] = "i.description as incident_message";
    $selectFields[] = "u.name as user_name";
    
    // Add image info if feedback_images table exists
    $imagesTableExists = $conn->query("SHOW TABLES LIKE 'feedback_images'");
    if ($imagesTableExists->num_rows > 0) {
        $selectFields[] = "fi.id as image_id";
        $selectFields[] = "fi.filename";
        $selectFields[] = "fi.filepath";
        $selectFields[] = "fi.file_size";
        $selectFields[] = "fi.file_type";
    }
    
    $query = "SELECT " . implode(", ", $selectFields) . "
    FROM incident_feedback f
    LEFT JOIN incidents i ON f.incident_id = i.id
    LEFT JOIN users u ON f.user_id = u.id";
    
    // Add feedback_images join if table exists
    if ($imagesTableExists->num_rows > 0) {
        $query .= " LEFT JOIN feedback_images fi ON f.id = fi.feedback_id";
    }
    
    $query .= " WHERE 1=1";
    
    $params = [];
    $types = '';
    
    // Add filters
    if ($incidentId) {
        $query .= " AND f.incident_id = ?";
        $params[] = $incidentId;
        $types .= 's';
    }
    
    if ($status) {
        $query .= " AND f.status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    // Add ordering and limits
    $query .= " ORDER BY f.created_at DESC LIMIT $limit OFFSET $offset";
    
    $stmt = $conn->prepare($query);
    
    // Bind parameters if any
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $feedback = [];
    while ($row = $result->fetch_assoc()) {
        $feedback[] = $row;
    }
    
    // Get summary statistics
    $statsQuery = "SELECT 
        COUNT(*) as total_feedback,
        AVG(rating) as avg_rating,
        AVG(response_time_rating) as avg_response_time,
        AVG(professionalism_rating) as avg_professionalism,
        AVG(effectiveness_rating) as avg_effectiveness,
        AVG(overall_satisfaction) as avg_satisfaction,
        COUNT(CASE WHEN rating >= 4 THEN 1 END) as positive_feedback,
        COUNT(CASE WHEN rating <= 2 THEN 1 END) as negative_feedback
    FROM incident_feedback";
    
    $statsStmt = $conn->prepare($statsQuery);
    $statsStmt->execute();
    $statsResult = $statsStmt->get_result();
    $stats = $statsResult->fetch_assoc();
    
    // Format feedback data
    $formattedFeedback = array_map(function($item) {
        return [
            'id' => $item['id'],
            'incident_id' => $item['incident_id'],
            'user_id' => $item['user_id'],
            'user_name' => $item['user_name'],
            'rating' => intval($item['rating']),
            'response_time_rating' => $item['response_time_rating'] ? intval($item['response_time_rating']) : null,
            'professionalism_rating' => $item['professionalism_rating'] ? intval($item['professionalism_rating']) : null,
            'effectiveness_rating' => $item['effectiveness_rating'] ? intval($item['effectiveness_rating']) : null,
            'overall_satisfaction' => $item['overall_satisfaction'] ? intval($item['overall_satisfaction']) : null,
            'feedback_text' => $item['feedback_text'],
            'admin_response' => $item['admin_response'],
            'evidence_image' => $item['evidence_image'],
            'category' => $item['category'],
            'status' => $item['status'],
            'created_at' => $item['created_at'],
            'incident_type' => $item['incident_type'],
            'incident_message' => $item['incident_message'],
            'image' => $item['image_id'] ? [
                'id' => $item['image_id'],
                'filename' => $item['filename'],
                'filepath' => $item['filepath'],
                'file_size' => $item['file_size'],
                'file_type' => $item['file_type']
            ] : null
        ];
    }, $feedback);
    
    echo json_encode([
        'success' => true,
        'feedback' => $formattedFeedback,
        'stats' => [
            'total_feedback' => intval($stats['total_feedback']),
            'avg_rating' => round(floatval($stats['avg_rating']), 2),
            'avg_response_time' => $stats['avg_response_time'] ? round(floatval($stats['avg_response_time']), 2) : null,
            'avg_professionalism' => $stats['avg_professionalism'] ? round(floatval($stats['avg_professionalism']), 2) : null,
            'avg_effectiveness' => $stats['avg_effectiveness'] ? round(floatval($stats['avg_effectiveness']), 2) : null,
            'avg_satisfaction' => $stats['avg_satisfaction'] ? round(floatval($stats['avg_satisfaction']), 2) : null,
            'positive_feedback' => intval($stats['positive_feedback']),
            'negative_feedback' => intval($stats['negative_feedback'])
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 