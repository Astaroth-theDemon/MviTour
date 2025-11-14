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
    $archive_sql = "UPDATE featured_attractions SET status = 'archived' WHERE attraction_id = $id";
    
    if ($conn->query($archive_sql) === TRUE) {
        $_SESSION['success_message'] = 'Featured attraction archived successfully!';
        header("Location: manage_featured_attractions.php");
        exit();
    } else {
        // If there's an error, redirect back with error message
        $_SESSION['error_message'] = "Error archiving featured attraction: " . $conn->error;
        header("Location: manage_featured_attractions.php");
        exit();
    }
}

// If no ID provided, redirect back
header("Location: manage_featured_attractions.php");
exit();
?>