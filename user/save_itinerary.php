<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

// Required fields validation
$required_fields = [
    'people',
    'destination',
    'start_date',
    'end_date',
    'duration',
    'budget',
    'activities',
    'sights',
    'include_dining', // New required field
    'need_accommodation'
];

foreach ($required_fields as $field) {
    if (!isset($data[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

// Additional validation for accommodation
if ($data['need_accommodation'] === 'yes' && !isset($data['accommodation'])) {
    echo json_encode(['success' => false, 'message' => 'Accommodation type is required when accommodation is needed']);
    exit;
}

// Additional validation for dining option
if (!in_array($data['include_dining'], ['yes', 'no'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid dining option value']);
    exit;
}

$user_id = $_SESSION['user_id'];
$itinerary_data = json_encode($data);

try {
    // Check if user has existing itineraries with the same date range
    $check_stmt = $conn->prepare("
        SELECT itinerary_id 
        FROM SavedItineraries 
        WHERE user_id = ? 
        AND JSON_UNQUOTE(JSON_EXTRACT(itinerary_data, '$.start_date')) = ?
        AND JSON_UNQUOTE(JSON_EXTRACT(itinerary_data, '$.end_date')) = ?
    ");
    
    $check_stmt->bind_param("iss", $user_id, $data['start_date'], $data['end_date']);
    $check_stmt->execute();
    $existing_result = $check_stmt->get_result();

    if ($existing_result->num_rows > 0) {
        // If exists, update the existing itinerary
        $existing_itinerary = $existing_result->fetch_assoc();
        $update_stmt = $conn->prepare("
            UPDATE SavedItineraries 
            SET itinerary_data = ?, 
                created_at = CURRENT_TIMESTAMP 
            WHERE itinerary_id = ? AND user_id = ?
        ");
        $update_stmt->bind_param("sii", $itinerary_data, $existing_itinerary['itinerary_id'], $user_id);
        
        if ($update_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Itinerary updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating itinerary']);
        }
    } else {
        // If not exists, insert new itinerary
        $insert_stmt = $conn->prepare("
            INSERT INTO SavedItineraries (user_id, itinerary_data) 
            VALUES (?, ?)
        ");
        $insert_stmt->bind_param("is", $user_id, $itinerary_data);
        
        if ($insert_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Itinerary saved successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error saving itinerary']);
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>