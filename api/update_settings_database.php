<?php
header('Content-Type: application/json');
session_start();
require_once '../config/db_connect.php';

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    $conn = getDBConnection();

    // Check if user is logged in and is admin
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized access');
    }

    // Check if user is admin
    $userId = $_SESSION['user_id'];
    $checkAdminSql = "SELECT type FROM users WHERE id = ?";
    $stmt = $conn->prepare($checkAdminSql);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user || $user['type'] !== 'admin') {
        throw new Exception('Admin access required');
    }

    // Add new default settings if they don't exist
    $newSettings = [
        ['keywords', 'sunog,fire,nasusunog,fire alert,emergency,disaster'],
        ['locations', 'Dasmariñas City,Cavite,Manila,Quezon City,Makati,Salawag,Paliparan,Burol,Langkaan,Sampaguita,Saint Peter,Saint Paul,Saint John,Saint Luke,Saint Mark,Saint Matthew,Saint James,Saint Thomas,Saint Andrew,Saint Philip,Saint Bartholomew,Saint Simon,Saint Jude,Saint Matthias,Saint Stephen,Saint Barnabas,Saint Timothy,Saint Titus,Saint Philemon,Emmanuel,San Jose,San Miguel,San Nicolas,San Agustin,San Isidro,San Lorenzo,San Antonio,San Vicente,San Rafael,San Gabriel,San Roque,San Francisco,San Pedro,San Pablo,San Mateo,San Lucas,San Marcos,San Juan,San Andres,San Felipe,San Bartolome,San Simon,San Judas,San Matias,San Esteban,San Bernabe,San Timoteo,San Tito,San Filemon'],
        ['query', 'fire emergency OR sunog OR fire alert OR emergency response'],
        ['notification_keywords', 'sunog,fire,emergency,disaster,alarm'],
        ['monitoring_locations', 'Dasmariñas City,Cavite,Salawag,Paliparan,Burol,Langkaan,Sampaguita,Saint Peter,Saint Paul,Saint John,Saint Luke,Saint Mark,Saint Matthew,Saint James,Saint Thomas,Saint Andrew,Saint Philip,Saint Bartholomew,Saint Simon,Saint Jude,Saint Matthias,Saint Stephen,Saint Barnabas,Saint Timothy,Saint Titus,Saint Philemon,Emmanuel,San Jose,San Miguel,San Nicolas,San Agustin,San Isidro,San Lorenzo,San Antonio,San Vicente,San Rafael,San Gabriel,San Roque,San Francisco,San Pedro,San Pablo,San Mateo,San Lucas,San Marcos,San Juan,San Andres,San Felipe,San Bartolome,San Simon,San Judas,San Matias,San Esteban,San Bernabe,San Timoteo,San Tito,San Filemon']
    ];

    $insertSql = "INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)";
    $stmt = $conn->prepare($insertSql);

    foreach ($newSettings as $setting) {
        $stmt->bind_param("ss", $setting[0], $setting[1]);
        $stmt->execute();
    }

    $response['success'] = true;
    $response['message'] = 'Settings database updated successfully';
    $response['data'] = $newSettings;

    closeDBConnection($conn);

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Settings database update error: ' . $e->getMessage());
}

echo json_encode($response);
?> 