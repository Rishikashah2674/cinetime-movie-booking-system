<?php
// Database connection parameters
$host = "localhost";  // Database server
$username = "root";   // Database username
$password = "";       // Database password (empty by default for localhost)
$database = "cinetime";  // Database name

// Establishing the database connection
$conn = mysqli_connect($host, $username, $password, $database,3307);

// Check if the connection is successful
if (!$conn) {
    // Connection failed, output the error message and terminate the script
    die("Connection failed: " . mysqli_connect_error());
} else {
    // Connection successful, you can proceed with your queries
    echo "Connected successfully to the database: " . $database;
}
?>