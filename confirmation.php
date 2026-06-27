<?php
session_start();
require_once('connection.php');

// Get booking ID from session or URL
$booking_id = $_SESSION['booking_id'] ?? $_GET['booking_id'] ?? null;

if (!$booking_id) {
    die("Booking ID missing.");
}

// Fetch booking and movie info
$sql = "SELECT b.*, m.title AS movie_title 
        FROM bookings b
        JOIN movies m ON b.movie_id = m.movie_id
        WHERE b.booking_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Booking not found.");
}

$booking = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Confirmation</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
        }
        .container {
            margin-top: 80px;
            background-color: #1f1f1f;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.6);
            max-width: 600px;
        }
        .btn-danger {
            background-color: #e50914;
            border: none;
        }
        .btn-danger:hover {
            background-color: #ff1f28;
        }
    </style>
</head>
<body>
<div class="container text-center">
    <h2 class="mb-4">🎉 Booking Confirmed!</h2>
    <p><strong>Booking ID:</strong> <?= $booking['booking_id'] ?></p>
    <p><strong>Movie:</strong> <?= $booking['movie_title'] ?></p>
    <p><strong>Theater:</strong> <?= $booking['theater'] ?></p>
    <p><strong>Showtime:</strong> <?= $booking['showtime'] ?></p>
    <p><strong>Tickets:</strong> <?= $booking['tickets'] ?></p>
    <p><strong>Seats:</strong> <?= $booking['seats'] ?></p>
    <p><strong>Payment Method:</strong> <?= ucfirst($booking['payment_method']) ?></p>

    <a href="download_ticket.php?booking_id=<?= $booking['booking_id'] ?>" class="btn btn-danger mt-4">
        🎫 Download Ticket (PDF)
    </a>
</div>
</body>
</html>
