<?php
session_start();
include_once "connection.php";

if (!isset($_GET['show_id']) || empty($_GET['show_id']) || !isset($_GET['tickets']) || empty($_GET['tickets'])) {
    die("<div class='alert alert-danger text-center'>Invalid session parameters! Please restart booking.</div>");
}

$show_id = intval($_GET['show_id']);
$tickets = intval($_GET['tickets']);

// Fetch Show Context
$sql = "
    SELECT sh.*, m.title, m.poster_url, t.name as theater_name, t.city, s.screen_name, s.id as screen_id 
    FROM shows sh
    JOIN movies m ON sh.movie_id = m.movie_id
    JOIN theaters t ON sh.theater_id = t.id
    JOIN screens s ON sh.screen_id = s.id
    WHERE sh.id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $show_id);
$stmt->execute();
$show = $stmt->get_result()->fetch_assoc();

if (!$show) die("Showtime no longer available.");
$screen_id = $show['screen_id'];

// Fetch Pricing Configuration
$pricing_res = $conn->query("SELECT seat_type, price FROM seat_pricing WHERE show_id = {$show_id}");
$prices = [];
while($pr = $pricing_res->fetch_assoc()) {
    $prices[$pr['seat_type']] = floatval($pr['price']);
}

// Fetch the Dynamic Physical Seat Map
$seats_res = $conn->query("SELECT * FROM seats WHERE screen_id = {$screen_id} ORDER BY row_name, seat_number");
$seat_map = [];
while ($s = $seats_res->fetch_assoc()) {
    $seat_map[$s['row_name']][] = $s;
}

// Fetch Already Booked Seats for this exactly Show (Hard Lock Logic)
$booked_res = $conn->query("
    SELECT bs.seat_id 
    FROM booked_seats bs
    JOIN bookings b ON bs.booking_id = b.booking_id
    WHERE b.show_id = {$show_id} AND (b.payment_status = 'Success' OR b.payment_status = 'Confirmed')
");
$booked_seats = [];
while ($bs = $booked_res->fetch_assoc()) {
    $booked_seats[] = $bs['seat_id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Seats - <?= htmlspecialchars($show['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #0a0a0f; color: white; font-family: 'Open Sans', sans-serif; }
        .screen-curve {
            background: linear-gradient(to bottom, #d1d5db, #f3f4f6);
            height: 40px; border-radius: 50% 50% 0 0 / 100% 100% 0 0;
            box-shadow: 0 10px 20px rgba(255,255,255,0.1); margin-bottom: 50px;
            text-align: center; color: #000; font-weight: 800; line-height: 40px;
            width: 80%; margin-left: auto; margin-right: auto;
        }
        
        /* Responsive Container */
        .seat-wrapper {
            width: 100%;
            overflow-x: auto; /* Horizontal scroll on mobile */
            padding: 10px 0 40px 0;
        }
        
        .seat-map-inner {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: max-content; /* Prevents rows from collapsing on tiny screens */
            margin: 0 auto;
            padding: 0 20px;
        }

        .seat-row {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
            gap: 6px; /* Dynamic spacing */
        }
        
        .row-label {
            width: 25px;
            font-weight: bold;
            color: #888;
            text-align: center;
            flex-shrink: 0;
        }

        /* Seat Base Styling */
        .seat {
            width: 32px; height: 32px; border-radius: 6px; cursor: pointer;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 600; transition: transform 0.2s, background 0.2s;
            flex-shrink: 0;
        }
        
        /* Colors as requested: Available -> Green, Booked -> Red, Selected -> Blue */
        .seat.available { 
            background: transparent; border: 1px solid #10b981; color: #10b981; 
        }
        .seat.available:hover { 
            background: rgba(16, 185, 129, 0.2); transform: scale(1.1); 
        }
        
        .seat.occupied { 
            background: #ef4444 !important; border: 1px solid #dc2626 !important; 
            color: #fff !important; cursor: not-allowed; opacity: 0.8;
        }
        
        .seat.selected { 
            background: #3b82f6 !important; border: 1px solid #2563eb !important; 
            color: #fff !important; box-shadow: 0 0 10px rgba(59, 130, 246, 0.6); transform: scale(1.05); 
        }
        
        .aisle-gap { width: 30px; flex-shrink: 0; }
        
        /* Media Queries for dynamic sizing */
        @media (max-width: 768px) {
            .seat { width: 28px; height: 28px; font-size: 10px; }
            .seat-row { gap: 4px; margin-bottom: 6px; }
            .aisle-gap { width: 20px; }
            .screen-curve { width: 95%; height: 30px; line-height: 30px; font-size: 0.8rem; }
        }
        
        .checkout-bar {
            position: fixed; bottom: 0; left: 0; width: 100%;
            background: rgba(20, 20, 28, 0.95); backdrop-filter: blur(10px);
            border-top: 1px solid #333; padding: 20px 0; z-index: 1000;
        }
    </style>
</head>
<body style="padding-bottom: 120px;">

<div class="container py-4">
    <div class="text-center mb-5">
        <h2 class="fw-bold text-white mb-2"><?= htmlspecialchars($show['title']) ?></h2>
        <p class="text-secondary mb-0">
            <i class="fas fa-map-marker-alt text-danger"></i> <?= htmlspecialchars($show['theater_name']) ?> (<?= htmlspecialchars($show['city']) ?>) &bull; 
            <i class="fas fa-tv text-info"></i> <?= htmlspecialchars($show['screen_name']) ?> &bull; 
            <i class="fas fa-calendar"></i> <?= date('d M, h:i A', strtotime($show['show_date'].' '.$show['show_time'])) ?>
        </p>
    </div>

    <!-- Color Legend -->
    <div class="d-flex justify-content-center gap-4 mb-5">
        <div class="text-secondary small d-flex align-items-center gap-2"><div class="seat available" style="width:20px;height:20px;cursor:default;"></div> Available</div>
        <div class="text-secondary small d-flex align-items-center gap-2"><div class="seat selected" style="width:20px;height:20px;cursor:default;"></div> Selected</div>
        <div class="text-secondary small d-flex align-items-center gap-2"><div class="seat occupied" style="width:20px;height:20px;cursor:default;"></div> Booked</div>
    </div>

    <div class="screen-curve">SCREEN</div>

    <?php if (empty($seat_map)): ?>
        <div class="alert alert-warning text-center">Theater management has not generated a seat map for this screening yet.</div>
    <?php else: ?>
        <div class="seat-wrapper">
            <div class="seat-map-inner">
                <?php foreach($seat_map as $row_name => $seats): ?>
                    <div class="seat-row">
                        <span class="row-label"><?= $row_name ?></span>
                        
                        <?php 
                        $half = ceil(count($seats) / 2);
                        $count = 0;
                        foreach($seats as $s): 
                            $count++;
                            $is_booked = in_array($s['id'], $booked_seats);
                            $price = $prices[$s['seat_type']] ?? $show['base_price'];
                            $ident = $row_name . $s['seat_number'];
                            $status_class = $is_booked ? 'occupied' : 'available';
                        ?>
                            <!-- Aisle Gap Logic -->
                            <?php if($count == $half && count($seats) > 6): ?>
                                <div class="aisle-gap"></div>
                            <?php endif; ?>
                            
                            <div class="seat <?= $status_class ?>" 
                                 data-seat-id="<?= $s['id'] ?>" 
                                 data-info="<?= $ident ?>" 
                                 data-price="<?= $price ?>"
                                 title="<?= $ident ?> (<?= $s['seat_type'] ?>: ₹<?= $price ?>)"
                                 onclick="selectSeat(this)">
                                <?= $s['seat_number'] ?>
                            </div>
                        <?php endforeach; ?>
                        
                        <span class="row-label"><?= $row_name ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="checkout-bar shadow-lg">
    <div class="container">
        <form action="payment.php" method="POST" class="d-flex justify-content-between align-items-center">
            <input type="hidden" name="show_id" value="<?= $show_id ?>">
            <input type="hidden" name="tickets" value="<?= $tickets ?>">
            <input type="hidden" name="selected_seats" id="selected_seats" value="">
            
            <div>
                <h5 class="mb-1 text-white">Target: <?= $tickets ?> Ticket(s)</h5>
                <span class="text-secondary small" id="selectionStatus">0 selected &bull; Total: ₹0</span>
            </div>
            
            <button type="submit" class="btn btn-danger px-5 fw-bold btn-lg" id="proceedBtn" disabled>Pay ₹<span id="btnTotal">0.00</span></button>
        </form>
    </div>
</div>

<script>
    let selectedSeats = [];
    let totalPrice = 0;
    const maxTickets = <?= $tickets ?>;
    
    function selectSeat(element) {
        if (element.classList.contains('occupied')) return;
        
        const seatId = element.getAttribute('data-seat-id');
        const price = parseFloat(element.getAttribute('data-price'));
        const ident = element.getAttribute('data-info');
        
        if (element.classList.contains('selected')) {
            element.classList.remove('selected');
            selectedSeats = selectedSeats.filter(s => s.id !== seatId);
            totalPrice -= price;
        } else {
            if (selectedSeats.length >= maxTickets) {
                // Remove the first picked to allow swapping
                const first = selectedSeats.shift();
                document.querySelector(`[data-seat-id="${first.id}"]`).classList.remove('selected');
                totalPrice -= first.price;
            }
            element.classList.add('selected');
            selectedSeats.push({id: seatId, ident: ident, price: price});
            totalPrice += price;
        }
        
        // Update UI
        const isReady = selectedSeats.length === maxTickets;
        document.getElementById('proceedBtn').disabled = !isReady;
        document.getElementById('btnTotal').textContent = totalPrice.toFixed(2);
        
        const count = selectedSeats.length;
        const labels = selectedSeats.map(s => s.ident).join(', ');
        document.getElementById('selectionStatus').innerHTML = `${count} selected (${labels || 'None'}) &bull; Total: ₹${totalPrice.toFixed(2)}`;
        
        // Send array of seat IDs to backend
        document.getElementById('selected_seats').value = JSON.stringify(selectedSeats.map(s => s.id));
    }
</script>

</body>
</html>
<?php $conn->close(); ?>
