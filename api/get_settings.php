<?php
require_once __DIR__ . '/../config/db_connect.php';
header('Content-Type: application/json');

$conn = getDBConnection();

// Get Apify query
$apifyQuery = '';
$apifyResult = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'apify_query' LIMIT 1");
if ($apifyResult && $row = $apifyResult->fetch_assoc()) {
    $apifyQuery = $row['setting_value'];
}

// Get NLP locations from DB
$locations = [];
$locResult = $conn->query("SELECT location FROM nlp_locations ORDER BY location ASC");
while ($row = $locResult->fetch_assoc()) {
    $locations[] = $row['location'];
}

// Add hardcoded locations (if any)
$hardcoded = [
    'Dasmariñas City', 'General Trias', 'Imus', 'Bacoor', 'Cavite City',
    'Tanza', 'Trece Martires', 'Tagaytay', 'Silang', 'Rosario', 'Naic',
    'Indang', 'Alfonso', 'Amadeo', 'Carmona', 'Gen. Mariano Alvarez',
    'Kawit', 'Magallanes', 'Maragondon', 'Mendez', 'Noveleta', 'Ternate'
];
foreach ($hardcoded as $loc) {
    if (!in_array($loc, $locations)) {
        $locations[] = $loc;
    }
}

sort($locations);

echo json_encode([
    'success' => true,
    'apify_query' => $apifyQuery,
    'locations' => $locations
]); 