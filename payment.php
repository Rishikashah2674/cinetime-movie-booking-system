<?php
session_start();
include_once "connection.php";
include_once "config_razorpay.php";

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("<script>alert('Please login to proceed with booking.'); window.location.href='login.php';</script>");
}
$user_id = $_SESSION['user_id'];

$show_id = intval($_POST['show_id'] ?? 0);
$tickets = intval($_POST['tickets'] ?? 0);
$selected_seats_json = $_POST['selected_seats'] ?? '[]';
$selected_seats_array = json_decode($selected_seats_json, true);

if (!$show_id || !$tickets || empty($selected_seats_array)) {
    die("Missing booking data. Please return to seat selection.");
}

// 1. Fetch Show Context & Pricing
$show_q = $conn->prepare("SELECT base_price FROM shows WHERE id = ?");
$show_q->bind_param("i", $show_id);
$show_q->execute();
$show_res = $show_q->get_result()->fetch_assoc();
if (!$show_res) die("Showtime invalid.");
$base_price = floatval($show_res['base_price']);

$pricing_q = $conn->query("SELECT seat_type, price FROM seat_pricing WHERE show_id = {$show_id}");
$prices = [];
while($pr = $pricing_q->fetch_assoc()) {
    $prices[$pr['seat_type']] = floatval($pr['price']);
}

// 2. Fetch specific selected seats info to calculate total and compile string
$seat_ids_comma = implode(',', array_map('intval', $selected_seats_array));
$seats_query = $conn->query("SELECT id, row_name, seat_number, seat_type FROM seats WHERE id IN ($seat_ids_comma)");

$total_price = 0;
$txt_labels = [];

while($s = $seats_query->fetch_assoc()) {
    $pr = $prices[$s['seat_type']] ?? $base_price;
    $total_price += $pr;
    $txt_labels[] = $s['row_name'] . $s['seat_number'];
}

$seats_text = implode(', ', $txt_labels);
$amount_paise = $total_price * 100; // Razorpay expects paise

// 3. Insert Pending Booking into 'bookings' table
$payment_method = "Razorpay";
$payment_status = "Pending";

$insert = $conn->prepare("INSERT INTO bookings (user_id, show_id, tickets, seats, total_price, payment_method, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
$insert->bind_param("iiisdss", $user_id, $show_id, $tickets, $seats_text, $total_price, $payment_method, $payment_status);

if (!$insert->execute()) {
    die("Error creating pending booking: " . $conn->error);
}
$booking_id = $conn->insert_id;

// Store seat IDs in session to lock them securely inside process_payment.php later
$_SESSION['pending_booking_seats_'.$booking_id] = $selected_seats_array;

$receipt_id = "receipt_booking_" . $booking_id;

// 4. Create Razorpay Order
$api_endpoint = 'https://api.razorpay.com/v1/orders';
$post_fields = json_encode([
    'amount' => $amount_paise,
    'currency' => 'INR',
    'receipt' => $receipt_id,
    'payment_capture' => 1 // Auto capture
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_endpoint);
curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY . ":" . RAZORPAY_SECRET);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if (curl_errno($ch)) {
    die('Failed to connect to Razorpay: ' . curl_error($ch));
}
curl_close($ch);

$order_data = json_decode($response, true);
if ($http_code != 200 || !isset($order_data['id'])) {
    die("Failed to create Razorpay Order. Error: " . ($order_data['error']['description'] ?? 'Unknown'));
}

$razorpay_order_id = $order_data['id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0a0a0f; color: #ffffff; }
        .container {
            max-width: 600px; margin-top: 80px;
            background-color: #14141c; padding: 40px;
            border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.8);
            border: 1px solid #2a2a35;
            text-align: center;
        }
        .razorpay-payment-button {
            background-color: #e50914; color: white; border: none; padding: 15px 30px;
            font-size: 1.2rem; border-radius: 8px; font-weight: bold; width: 100%; margin-top: 20px;
            cursor: pointer; transition: 0.3s;
        }
        .razorpay-payment-button:hover {
            background-color: #ff1f28; transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(229, 9, 20, 0.4);
        }
    </style>
</head>
<body>
<div class="container">
    <h3 class="mb-4 text-danger fw-bold">Secure Checkout</h3>
    
    <div class="bg-dark p-3 rounded mb-4 text-start border border-secondary">
        <div class="d-flex justify-content-between mb-2">
            <span class="text-secondary">Quantity:</span>
            <span class="fw-bold"><?= $tickets ?> Ticket(s)</span>
        </div>
        <div class="d-flex justify-content-between mb-2">
            <span class="text-secondary">Selected Seats:</span>
            <span class="fw-bold"><?= htmlspecialchars($seats_text) ?></span>
        </div>
        <hr class="border-secondary">
        <div class="d-flex justify-content-between fs-5">
            <span class="text-secondary">Total Payable:</span>
            <span class="fw-bold text-success">₹<?= number_format($total_price, 2) ?></span>
        </div>
    </div>
    
    <!-- Razorpay Checkout -->
    <form action="process_payment.php" method="POST">
        <input type="hidden" name="booking_id" value="<?= $booking_id ?>">
        
        <script
            src="https://checkout.razorpay.com/v1/checkout.js"
            data-key="<?= RAZORPAY_KEY ?>"
            data-amount="<?= $amount_paise ?>"
            data-currency="INR"
            data-order_id="<?= $razorpay_order_id ?>"
            data-buttontext="Pay ₹<?= number_format($total_price, 2) ?> Securely"
            data-name="CineTime Premium"
            data-description="Dynamic Seat Booking"
            data-theme.color="#e50914"
            data-prefill.name="Current User" 
            data-prefill.contact="9999999999">
        </script>
    </form>
</div>
</body>
</html>
