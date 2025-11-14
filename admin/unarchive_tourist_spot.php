<?php
include '../db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_adminacc.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Update the status to 'active'
    $unarchive_sql = "UPDATE Tourist_Spots SET status = 'active' WHERE tourist_spot_id = $id";
    
    if ($conn->query($unarchive_sql) === TRUE) {
        $_SESSION['success_message'] = 'Tourist spot unarchived successfully!';
        header("Location: archived_tourist_spots.php");
        exit();
    } else {
        // If there's an error, redirect back with error message
        $_SESSION['error_message'] = "Error unarchiving tourist spot: " . $conn->error;
        header("Location: archived_tourist_spots.php");
        exit();
    }
}

// If no ID provided, redirect back
header("Location: archived_tourist_spots.php");
exit();
?>