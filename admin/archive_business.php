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
    
    // Update the status to 'archived'
    $archive_sql = "UPDATE Businesses SET status = 'archived' WHERE business_id = $id";
    
    if ($conn->query($archive_sql) === TRUE) {
        $_SESSION['success_message'] = 'Business archived successfully!';
        header("Location: manage_business.php");
        exit();
    } else {
        // If there's an error, redirect back with error message
        $_SESSION['error_message'] = "Error archiving business: " . $conn->error;
        header("Location: manage_business.php");
        exit();
    }
}

// If no ID provided, redirect back
header("Location: manage_business.php");
exit();
?>