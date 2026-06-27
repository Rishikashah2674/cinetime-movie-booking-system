<?php
$host = "localhost";
$username = "root";
$password = ""; // default for XAMPP
$database = "cinetime";

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
