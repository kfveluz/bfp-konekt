<?php
header('Content-Type: application/json');
require_once '../config/db_connect.php';

$conn = getDBConnection();

// Fetch ALL incidents (both active and resolved) for proper separation in frontend
$sql = "SELECT * FROM incidents ORDER BY created_at DESC";
$result = $conn->query($sql);

$incidents = [];
$seenUrls = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $url = $row['url'] ?? '';
        if ($url && !in_array($url, $seenUrls)) {
            $incidents[] = $row;
            $seenUrls[] = $url;
        } elseif (!$url) {
            // If no URL, include the incident (optional: you can skip if you want)
            $incidents[] = $row;
        }
    }
}

closeDBConnection($conn);

echo json_encode([
    'success' => true,
    'incidents' => $incidents
]); 