<?php
// db_connect.php
// This file establishes a connection to your MySQL database.

$servername = "localhost";
$username = "root";     // IMPORTANT: Replace with your MySQL database username
$password = "";         // IMPORTANT: Replace with your MySQL database password
$dbname = "motorcontrol"; // IMPORTANT: Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // If connection fails, terminate script and display error
    die("Connection failed: " . $conn->connect_error);
}
?>
