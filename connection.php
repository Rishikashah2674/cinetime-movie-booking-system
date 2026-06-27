<?php
// Database connection parameters
$host = "127.0.0.1";   // Use IP instead of localhost
$username = "root";
$password = "";
$database = "cinetime";
$port = 3306; // Change to 3307 if your MySQL uses different port

// Enable error reporting (important for debugging)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Create connection
    $conn = mysqli_connect($host, $username, $password, $database, $port);

    // Set charset (good practice)
    mysqli_set_charset($conn, "utf8mb4");

} catch (mysqli_sql_exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>