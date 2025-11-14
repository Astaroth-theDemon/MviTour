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
    $unarchive_sql = "UPDATE Businesses SET status = 'active' WHERE business_id = $id";
    
    if ($conn->query($unarchive_sql) === TRUE) {
        $_SESSION['success_message'] = 'Business unarchived successfully!';
        header("Location: archived_businesses.php");
        exit();
    } else {
        // If there's an error, redirect back with error message
        $_SESSION['error_message'] = "Error unarchiving business: " . $conn->error;
        header("Location: archived_businesses.php");
        exit();
    }
}

// If no ID provided, redirect back
header("Location: archived_businesses.php");
exit();
?>