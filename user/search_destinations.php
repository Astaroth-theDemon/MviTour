<?php
include '../db.php';

header('Content-Type: application/json');

if (isset($_GET['query'])) {
    $query = $_GET['query'];
    $results = array();

    // Search in tourist spots
    $sql_tourist = "SELECT 
        tourist_spot_id as id, 
        name, 
        category, 
        barangay, 
        location, 
        destination_thumbnail, 
        'tourist' as type 
    FROM Tourist_Spots 
    WHERE (name LIKE ? OR category LIKE ? OR barangay LIKE ? OR location LIKE ?) 
    AND status = 'active' 
    LIMIT 5";

    $searchTerm = "%" . $query . "%";
    $stmt = $conn->prepare($sql_tourist);
    $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $tourist_results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $results = array_merge($results, $tourist_results);

    // Search in businesses
    $sql_business = "SELECT 
        business_id as id, 
        name, 
        category, 
        barangay, 
        location, 
        destination_thumbnail, 
        'business' as type 
    FROM Businesses 
    WHERE (name LIKE ? OR category LIKE ? OR barangay LIKE ? OR location LIKE ?) 
    AND status = 'active' 
    LIMIT 5";

    $stmt = $conn->prepare($sql_business);
    $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $business_results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $results = array_merge($results, $business_results);

    echo json_encode($results);
} else {
    echo json_encode([]);
}
?>