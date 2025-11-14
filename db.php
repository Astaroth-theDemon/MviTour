<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mvitour_db";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
