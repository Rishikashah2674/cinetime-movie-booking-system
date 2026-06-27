<?php
require_once('connection.php');
require_once('tcpdf/tcpdf.php');

// Get booking_id from URL
$booking_id = $_GET['booking_id'] ?? null;

if (!$booking_id) {
    die("Booking ID missing.");
}

// Fetch booking and movie details
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

// Create new PDF document
$pdf = new TCPDF();
$pdf->SetCreator('CineTime');
$pdf->SetAuthor('CineTime');
$pdf->SetTitle('Movie Ticket');
$pdf->SetMargins(20, 20, 20);
$pdf->AddPage();

// Ticket HTML content
$html = '
<style>
    .header {
        font-size: 20px;
        font-weight: bold;
        color: #e50914;
    }
    .details {
        font-size: 14px;
        line-height: 1.5;
        color: #333;
    }
    .label {
        font-weight: bold;
        color: #000;
    }
</style>

<div class="header">CineTime Movie Ticket</div>
<br><br>
<div class="details">
    <span class="label">Booking ID:</span> ' . $booking['booking_id'] . '<br>
    <span class="label">Movie:</span> ' . $booking['movie_title'] . '<br>
    <span class="label">Theater:</span> ' . $booking['theater'] . '<br>
    <span class="label">Showtime:</span> ' . $booking['showtime'] . '<br>
    <span class="label">Tickets:</span> ' . $booking['tickets'] . '<br>
    <span class="label">Seats:</span> ' . $booking['seats'] . '<br>
    <span class="label">Payment Method:</span> ' . ucfirst($booking['payment_method']) . '
</div>
<br><br>
<div style="text-align:center; font-size:12px; color:gray;">
    Please arrive at least 15 minutes early. Thank you for choosing CineTime!
</div>
';

// Output PDF content
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('ticket_' . $booking_id . '.pdf', 'D'); // D = Download
