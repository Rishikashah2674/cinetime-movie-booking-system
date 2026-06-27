<?php
session_start();

// Ensure the user is logged in as an admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../admin_login.php");
    exit;
}

// Ensure the database connection is available for all admin scripts
require_once __DIR__ . '/../../connection.php';
?>
