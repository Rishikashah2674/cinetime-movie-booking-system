<?php
session_start();
include_once "connection.php";
include_once "config_razorpay.php";

if (!isset($_POST['razorpay_payment_id']) || !isset($_POST['razorpay_signature'])) {
    die("<div style='color:red; text-align:center; padding:50px;'><h3>Payment Failed or Cancelled.</h3><p>Could not retrieve payment details.</p></div>");
}

$razorpay_payment_id = $_POST['razorpay_payment_id'];
$razorpay_order_id = $_POST['razorpay_order_id'];
$razorpay_signature = $_POST['razorpay_signature'];
$booking_id = intval($_POST['booking_id']);

// Verify Signature
$generated_signature = hash_hmac('sha256', $razorpay_order_id . "|" . $razorpay_payment_id, RAZORPAY_SECRET);

if ($generated_signature === $razorpay_signature) {
    // Payment is successful and signature is valid

    // Fetch booking details
    $booking_query = $conn->prepare("SELECT total_price, user_id, seats FROM bookings WHERE booking_id = ?");
    $booking_query->bind_param("i", $booking_id);
    $booking_query->execute();
    $booking_result = $booking_query->get_result();
    $booking = $booking_result->fetch_assoc();

    if ($booking) {
        $amount = $booking['total_price'];
        $user_id = $booking['user_id'];
        $selected_seats = $booking['seats'];

        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Update booking status
            $update_booking = $conn->prepare("UPDATE bookings SET payment_status = 'Confirmed' WHERE booking_id = ?");
            $update_booking->bind_param("i", $booking_id);
            $update_booking->execute();

            // Insert into payments table
            $insert_payment = $conn->prepare("INSERT INTO payments (booking_id, user_id, razorpay_payment_id, razorpay_order_id, amount, status) VALUES (?, ?, ?, ?, ?, 'Success')");
            $insert_payment->bind_param("iissd", $booking_id, $user_id, $razorpay_payment_id, $razorpay_order_id, $amount);
            $insert_payment->execute();
            // Insert into booked_seats to hard-lock the physical seats
            if (isset($_SESSION['pending_booking_seats_'.$booking_id])) {
                $seat_array = $_SESSION['pending_booking_seats_'.$booking_id];
                $lock_stmt = $conn->prepare("INSERT INTO booked_seats (booking_id, seat_id) VALUES (?, ?)");
                foreach ($seat_array as $phy_seat_id) {
                    $lock_stmt->bind_param("ii", $booking_id, $phy_seat_id);
                    $lock_stmt->execute();
                }
                unset($_SESSION['pending_booking_seats_'.$booking_id]);
            }
            
            $conn->commit();
            $payment_status = "Payment Successful";
            
            // Set session for confirmation page
            $_SESSION['booking_id'] = $booking_id;
            $_SESSION['booking_id'] = $booking_id;

        } catch (Exception $e) {
            $conn->rollback();
            die("Database Error: " . $e->getMessage());
        }
    } else {
        die("Booking not found.");
    }
} else {
    // Signature Invalid
    $update_booking = $conn->prepare("UPDATE bookings SET payment_status = 'Failed' WHERE booking_id = ?");
    $update_booking->bind_param("i", $booking_id);
    $update_booking->execute();
    
    die("<div style='color:red; text-align:center; padding:50px;'><h3>Payment Validation Failed!</h3><p>Transaction may be fraudulent or corrupted.</p></div>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Success</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        body { background-color: #121212; color: #fff; }
        .container {
            margin-top: 80px; padding: 30px; max-width: 600px;
            background: #1f1f1f; border-radius: 15px; box-shadow: 0 0 10px rgba(0,0,0,0.6);
        }
        .btn { border-radius: 8px; }
    </style>
</head>
<body>
<div class="container text-center">
    <h2 class="mb-4 text-success">✅ <?= $payment_status ?></h2>
    <p>Your booking was successfully processed and confirmed!</p>
    <p><strong>Booking ID:</strong> <?= $booking_id ?></p>
    <p><strong>Transaction ID:</strong> <?= htmlspecialchars($razorpay_payment_id) ?></p>
    <p><strong>Seats:</strong> <?= htmlspecialchars($selected_seats) ?></p>
    <a href="confirmation.php?booking_id=<?= $booking_id ?>" class="btn btn-success mt-4">Continue to Confirmation</a>
</div>
</body>
</html>
