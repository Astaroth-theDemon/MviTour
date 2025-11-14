<?php
include '../db.php';

// Fetch tourist spots
$sql_tourist = "SELECT 
    tourist_spot_id as id, 
    'tourist' as type, 
    name, 
    category, 
    barangay, 
    location, 
    latitude, 
    longitude,
    destination_thumbnail 
FROM Tourist_Spots 
WHERE status = 'active'";

// Fetch all businesses
$sql_business = "SELECT 
    business_id as id, 
    'business' as type, 
    name, 
    category, 
    barangay, 
    location, 
    latitude, 
    longitude,
    destination_thumbnail 
FROM Businesses 
WHERE status = 'active'";

$result_tourist = $conn->query($sql_tourist);
$result_business = $conn->query($sql_business);

$destinations = [];

while ($row = $result_tourist->fetch_assoc()) {
    $destinations[] = $row;
}

while ($row = $result_business->fetch_assoc()) {
    $destinations[] = $row;
}

header('Content-Type: application/json');
echo json_encode($destinations);
?>