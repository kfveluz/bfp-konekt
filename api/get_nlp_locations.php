<?php
require_once __DIR__ . '/../config/db_connect.php';
header('Content-Type: application/json');

$conn = getDBConnection();
$locations = [];
$locResult = $conn->query("SELECT location FROM nlp_locations ORDER BY location ASC");
while ($row = $locResult->fetch_assoc()) {
    $locations[] = $row['location'];
}
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
echo json_encode(['success' => true, 'locations' => $locations]); 