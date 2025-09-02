<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/../config/db_connect.php'); // Include DB connection if needed later

// Define coordinates for common Dasmariñas locations
$locations = [
    'salawag' => ['lat' => 14.3166, 'lng' => 120.9166], // Approximate center of Salawag
    'paliparan' => ['lat' => 14.2908, 'lng' => 120.9458], // Approximate center of Paliparan
    'burol' => ['lat' => 14.325, 'lng' => 120.933], // Approximate center of Burol
    'langkaan' => ['lat' => 14.3406, 'lng' => 120.9544], // Approximate center of Langkaan
    'sampaguita' => ['lat' => 14.334, 'lng' => 120.916], // Approximate center of Sampaguita
    'dasma' => ['lat' => 14.323, 'lng' => 120.941], // Approximate center of Dasmariñas City
    'dasmariñas' => ['lat' => 14.323, 'lng' => 120.941], // Approximate center of Dasmariñas City
    // Add more locations as needed
];

// Default coordinates for Dasmariñas City center
$defaultCoords = ['lat' => 14.323, 'lng' => 120.941];

$query = trim($_GET['q'] ?? '');
$queryLower = strtolower($query);

$coordinates = null;
$message = '';

if (!empty($query)) {
    if (array_key_exists($queryLower, $locations)) {
        $coordinates = $locations[$queryLower];
    } else {
        // If specific location not found, check if query contains any known location
        $foundKnownLocation = false;
        foreach ($locations as $key => $coords) {
            if (strpos($queryLower, $key) !== false) {
                $coordinates = $coords;
                $foundKnownLocation = true;
                break;
            }
        }

        // If no specific or partial match, use default coords
        if (!$foundKnownLocation) {
             $coordinates = $defaultCoords;
             $message = "Approximate coordinates for Dasmariñas.";
        }
    }
} else {
    // If query is empty, return default coords with a message
    $coordinates = $defaultCoords;
    $message = "Empty query, returning approximate coordinates for Dasmariñas.";
}

if ($coordinates) {
    echo json_encode([
        'success' => true,
        'coordinates' => $coordinates,
        'message' => $message
    ]);
} else {
    // Should not happen with default coords, but as a fallback
    echo json_encode([
        'success' => false,
        'message' => 'Could not determine coordinates.'
    ]);
} 