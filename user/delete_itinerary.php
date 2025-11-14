<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['itinerary_id'])) {
    header("Location: my_itineraries.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$itinerary_id = $_POST['itinerary_id'];

// Delete the itinerary
$stmt = $conn->prepare("DELETE FROM SavedItineraries WHERE itinerary_id = ? AND user_id = ?");
$stmt->bind_param("ii", $itinerary_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting itinerary']);
}
?>