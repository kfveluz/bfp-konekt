<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once(__DIR__ . '/../config/db_connect.php');

// Enhanced coordinates for Dasmariñas locations
$locations = [
    // Barangays
    'salawag' => ['lat' => 14.3345, 'lng' => 120.9423, 'name' => 'Barangay Salawag'],
    'paliparan' => ['lat' => 14.3278, 'lng' => 120.9289, 'name' => 'Barangay Paliparan'],
    'burol' => ['lat' => 14.3312, 'lng' => 120.9315, 'name' => 'Barangay Burol'],
    'langkaan' => ['lat' => 14.3406, 'lng' => 120.9544, 'name' => 'Barangay Langkaan'],
    'sampaguita' => ['lat' => 14.334, 'lng' => 120.916, 'name' => 'Barangay Sampaguita'],
    'sampaloc' => ['lat' => 14.3321, 'lng' => 120.9345, 'name' => 'Barangay Sampaloc'],
    'san agustin' => ['lat' => 14.3289, 'lng' => 120.9332, 'name' => 'Barangay San Agustin'],
    'san jose' => ['lat' => 14.3301, 'lng' => 120.9356, 'name' => 'Barangay San Jose'],
    'san miguel' => ['lat' => 14.3315, 'lng' => 120.9328, 'name' => 'Barangay San Miguel'],
    'san nicolas' => ['lat' => 14.3298, 'lng' => 120.9341, 'name' => 'Barangay San Nicolas'],
    'santa cruz' => ['lat' => 14.3305, 'lng' => 120.9337, 'name' => 'Barangay Santa Cruz'],
    'santa fe' => ['lat' => 14.3318, 'lng' => 120.9321, 'name' => 'Barangay Santa Fe'],
    'santa lucia' => ['lat' => 14.3309, 'lng' => 120.9348, 'name' => 'Barangay Santa Lucia'],
    'santa maria' => ['lat' => 14.3312, 'lng' => 120.9334, 'name' => 'Barangay Santa Maria'],
    'santo cristo' => ['lat' => 14.3307, 'lng' => 120.9325, 'name' => 'Barangay Santo Cristo'],
    'santo niño' => ['lat' => 14.3315, 'lng' => 120.9342, 'name' => 'Barangay Santo Niño'],
    'vergera' => ['lat' => 14.3323, 'lng' => 120.9318, 'name' => 'Barangay Vergara'],
    'zapote' => ['lat' => 14.3295, 'lng' => 120.9351, 'name' => 'Barangay Zapote'],
    
    // Major establishments
    'sm city dasmariñas' => ['lat' => 14.3289, 'lng' => 120.9372, 'name' => 'SM City Dasmariñas'],
    'robinsons place dasmariñas' => ['lat' => 14.3302, 'lng' => 120.9365, 'name' => 'Robinsons Place Dasmariñas'],
    'walter mart dasmariñas' => ['lat' => 14.3315, 'lng' => 120.9358, 'name' => 'Walter Mart Dasmariñas'],
    'citymall dasmariñas' => ['lat' => 14.3298, 'lng' => 120.9368, 'name' => 'CityMall Dasmariñas'],
    'southwoods mall' => ['lat' => 14.3275, 'lng' => 120.9395, 'name' => 'Southwoods Mall'],
    'the district dasmariñas' => ['lat' => 14.3305, 'lng' => 120.9375, 'name' => 'The District Dasmariñas'],
    'vista mall dasmariñas' => ['lat' => 14.3285, 'lng' => 120.9385, 'name' => 'Vista Mall Dasmariñas'],
    
    // Schools and universities
    'de la salle university dasmariñas' => ['lat' => 14.3298, 'lng' => 120.9385, 'name' => 'De La Salle University Dasmariñas'],
    'dlsu-d' => ['lat' => 14.3298, 'lng' => 120.9385, 'name' => 'DLSU-D'],
    'dlsu medical center' => ['lat' => 14.3305, 'lng' => 120.9389, 'name' => 'DLSU Medical Center'],
    'dlsumc' => ['lat' => 14.3305, 'lng' => 120.9389, 'name' => 'DLSUMC'],
    'emilio aguinaldo college' => ['lat' => 14.3312, 'lng' => 120.9378, 'name' => 'Emilio Aguinaldo College'],
    'philippine christian university' => ['lat' => 14.3308, 'lng' => 120.9362, 'name' => 'Philippine Christian University'],
    'pcu' => ['lat' => 14.3308, 'lng' => 120.9362, 'name' => 'PCU'],
    
    // Government buildings
    'dasmariñas city hall' => ['lat' => 14.3294, 'lng' => 120.9367, 'name' => 'Dasmariñas City Hall'],
    'bfp dasmariñas' => ['lat' => 14.3294, 'lng' => 120.9367, 'name' => 'BFP Dasmariñas City Fire Station'],
    'bfp dasmariñas city fire station' => ['lat' => 14.3294, 'lng' => 120.9367, 'name' => 'BFP Dasmariñas City Fire Station'],
    
    // Major roads
    'aguinaldo highway' => ['lat' => 14.3294, 'lng' => 120.9367, 'name' => 'Aguinaldo Highway'],
    'governor\'s drive' => ['lat' => 14.3294, 'lng' => 120.9367, 'name' => 'Governor\'s Drive'],
    'congressional road' => ['lat' => 14.3294, 'lng' => 120.9367, 'name' => 'Congressional Road'],
    'molino road' => ['lat' => 14.3294, 'lng' => 120.9367, 'name' => 'Molino Road'],
    
    // General areas
    'dasma' => ['lat' => 14.3294, 'lng' => 120.9367, 'name' => 'Dasmariñas City'],
    'dasmariñas' => ['lat' => 14.3294, 'lng' => 120.9367, 'name' => 'Dasmariñas City'],
    'dasmariñas city' => ['lat' => 14.3294, 'lng' => 120.9367, 'name' => 'Dasmariñas City'],
    'dasmariñas cavite' => ['lat' => 14.3294, 'lng' => 120.9367, 'name' => 'Dasmariñas, Cavite'],
    'cavite' => ['lat' => 14.3294, 'lng' => 120.9367, 'name' => 'Cavite Province']
];

// Default coordinates for Dasmariñas City center
$defaultCoords = ['lat' => 14.3294, 'lng' => 120.9367, 'name' => 'Dasmariñas City Center'];

$query = trim($_GET['q'] ?? '');
$queryLower = strtolower($query);

$coordinates = null;
$message = '';
$confidence = 'low';

if (!empty($query)) {
    // Direct match
    if (array_key_exists($queryLower, $locations)) {
        $coordinates = $locations[$queryLower];
        $confidence = 'high';
        $message = "Exact match found for: {$coordinates['name']}";
    } else {
        // Partial match
        $bestMatch = null;
        $bestScore = 0;
        
        foreach ($locations as $key => $coords) {
            // Check if query contains the location
            if (strpos($queryLower, $key) !== false) {
                $score = strlen($key); // Longer matches get higher scores
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = $coords;
                }
            }
            
            // Check if location contains the query
            if (strpos($key, $queryLower) !== false) {
                $score = strlen($queryLower);
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = $coords;
                }
            }
        }
        
        if ($bestMatch) {
            $coordinates = $bestMatch;
            $confidence = 'medium';
            $message = "Partial match found for: {$coordinates['name']}";
        } else {
            // Check for barangay patterns
            $barangayPatterns = [
                '/brgy\.?\s*(\w+)/i',
                '/barangay\s+(\w+)/i',
                '/bgy\.?\s*(\w+)/i'
            ];
            
            foreach ($barangayPatterns as $pattern) {
                if (preg_match($pattern, $query, $matches)) {
                    $barangayName = strtolower($matches[1]);
                    if (array_key_exists($barangayName, $locations)) {
                        $coordinates = $locations[$barangayName];
                        $confidence = 'medium';
                        $message = "Barangay pattern match: {$coordinates['name']}";
                        break;
                    }
                }
            }
            
            // If still no match, use default
            if (!$coordinates) {
                $coordinates = $defaultCoords;
                $confidence = 'low';
                $message = "No specific location found, using Dasmariñas City Center";
            }
        }
    }
} else {
    $coordinates = $defaultCoords;
    $confidence = 'low';
    $message = "Empty query, using Dasmariñas City Center";
}

if ($coordinates) {
    echo json_encode([
        'success' => true,
        'coordinates' => [
            'lat' => $coordinates['lat'],
            'lng' => $coordinates['lng']
        ],
        'name' => $coordinates['name'],
        'confidence' => $confidence,
        'message' => $message,
        'query' => $query
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Could not determine coordinates.',
        'query' => $query
    ]);
}
?> 