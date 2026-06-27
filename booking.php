<?php
session_start();
include_once "connection.php";

if (!isset($_GET['movie_id']) || empty($_GET['movie_id'])) {
    die("Movie not found!");
}

$movie_id = intval($_GET['movie_id']);
date_default_timezone_set('Asia/Kolkata'); // Required for accurate past show disabling

$sql = "SELECT * FROM movies WHERE movie_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$movie = $stmt->get_result()->fetch_assoc();

if (!$movie) {
    die("Movie not found!");
}

$city = isset($_GET['city']) ? trim($_GET['city']) : '';

// Fetch active shows grouping them logically, getting available seats and BookMyShow layout
$shows_sql = "
    SELECT sh.*, t.name as theater_name, t.city, s.screen_name, s.total_seats,
           (SELECT COUNT(*) FROM booked_seats bs JOIN bookings b ON bs.booking_id = b.booking_id WHERE b.show_id = sh.id AND (b.payment_status = 'Success' OR b.payment_status = 'Confirmed')) as booked_count
    FROM shows sh
    JOIN theaters t ON sh.theater_id = t.id
    JOIN screens s ON sh.screen_id = s.id
    WHERE sh.movie_id = ? AND sh.show_date >= CURDATE()
";

if (!empty($city)) {
    $shows_sql .= " AND t.city = ? ";
}

$shows_sql .= " ORDER BY sh.show_date ASC, t.city ASC, t.name ASC, sh.show_time ASC";

$stmt = $conn->prepare($shows_sql);

if (!empty($city)) {
    $stmt->bind_param("is", $movie_id, $city);
} else {
    $stmt->bind_param("i", $movie_id);
}

$stmt->execute();
$res = $stmt->get_result();

$shows_by_date = [];
while ($row = $res->fetch_assoc()) {
    $date = date('Y-m-d', strtotime($row['show_date'])); // Group by strict date
    $theater_key = $row['theater_name'] . ' (' . $row['city'] . ')'; // Group by theater
    $shows_by_date[$date][$theater_key][] = $row;
}
$target_date = isset($_GET['date']) ? trim($_GET['date']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Tickets - <?= htmlspecialchars($movie['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #0a0a0f; color: white; font-family: 'Open Sans', sans-serif; }
        .hero { background: #14141c; border-bottom: 1px solid #2a2a35; padding: 40px 0; }
        
        .date-tabs .nav-link { 
            color: #ccc; border: 1px solid transparent; border-radius: 8px; 
            padding: 10px 15px; margin-right: 15px; text-align: center;
            background: #1e1e28;
        }
        .date-tabs .nav-link.active { background: #f84464; color: #fff; font-weight: bold; border-color: #f84464; }
        .date-tabs .nav-link:hover:not(.active) { color: #fff; background: #2a2a35; }
        
        .theater-row { border-bottom: 1px solid #2a2a35; padding: 25px 0; display: flex; align-items: flex-start; }
        .theater-info { width: 300px; padding-right: 20px; }
        .theater-info h5 { margin: 0; font-size: 1.1rem; color: #e5e5e5; }
        .theater-info .city { font-size: 0.8rem; color: #888; }
        
        .timings-container { flex-grow: 1; display: flex; flex-wrap: wrap; gap: 15px; }
        
        .time-btn-container { position: relative; }
        
        .time-btn {
            background: transparent; color: #4ade80; border: 1px solid #4ade80;
            padding: 8px 20px; border-radius: 4px; font-size: 0.95rem; font-weight: 500;
            cursor: pointer; transition: 0.2s; text-align: center; display: inline-block;
        }
        .time-btn:hover:not(.disabled) { background: rgba(74, 222, 128, 0.1); color: #4ade80; }
        .time-btn.selected { background: #f84464 !important; border-color: #f84464 !important; color: #fff !important; }
        
        .time-btn.fast-filling { color: #fbbf24; border-color: #fbbf24; }
        .time-btn.fast-filling:hover:not(.disabled) { background: rgba(251, 191, 36, 0.1); }
        
        .time-btn.disabled { color: #555; border-color: #333; background: #1a1a1a; cursor: not-allowed; text-decoration: line-through; }
        .time-btn small { display: block; font-size: 0.7rem; margin-top: 2px; }
        
        .fast-fill-tag {
            position: absolute; top: -10px; left: 50%; transform: translateX(-50%);
            background: #fbbf24; color: #000; font-size: 0.65rem; font-weight: bold;
            padding: 1px 6px; border-radius: 4px; white-space: nowrap; z-index: 2;
        }

        .checkout-bar {
            position: fixed; bottom: 0; left: 0; width: 100%;
            background: rgba(20, 20, 28, 0.95); backdrop-filter: blur(10px);
            border-top: 1px solid #333; padding: 20px 0; z-index: 1000; transform: translateY(100%); transition: transform 0.3s;
        }
        .checkout-bar.active { transform: translateY(0); }
    </style>
</head>
<body style="padding-bottom: 100px;">

<div class="hero mb-4 shadow-sm">
    <div class="container">
        <h2 class="fw-bold mb-3"><?= htmlspecialchars($movie['title']) ?></h2>
        <div>
            <span class="badge bg-secondary me-2 px-3 py-2 text-uppercase"><?= htmlspecialchars($movie['language']) ?></span>
            <span class="badge border border-secondary text-secondary px-3 py-2 text-uppercase"><?= htmlspecialchars($movie['genre']) ?></span>
            <span class="ms-3 text-warning"><i class="fas fa-heart"></i> <?= number_format($movie['price'] ?? 4.5, 1); ?>/5 (Rating)</span>
        </div>
    </div>
</div>

<div class="container">
    <?php if (empty($shows_by_date)): ?>
        <div class="alert justify-content-center d-flex align-items-center" style="background:#222; border:1px solid #444; color:#ccc;">
            <i class="fas fa-calendar-times fs-4 me-3 text-warning"></i> 
            <div>No upcoming shows scheduled for this movie in Gujarat currently. Please check back later.</div>
        </div>
    <?php else: ?>
        <form action="seat_selection.php" method="GET" id="bookingForm">
            <input type="hidden" name="show_id" id="show_id" value="" required>
            
            <!-- Date Filter Tabs -->
            <ul class="nav nav-tabs date-tabs mb-4 border-0" id="dateTabs">
                <?php $tab_idx = 0; foreach(array_keys($shows_by_date) as $date_key): 
                    $date_display = date('D, d M', strtotime($date_key));
                    $parts = explode(', ', $date_display);
                    $is_active = ($target_date === $date_key) || (empty($target_date) && $tab_idx === 0);
                ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $is_active ? 'active' : '' ?>" data-bs-toggle="tab" href="#date-<?= $tab_idx ?>">
                            <div style="font-size: 0.8rem; text-transform: uppercase;"><?= $parts[0] ?? '' ?></div>
                            <div class="fw-bold fs-5"><?= $parts[1] ?? $date_display ?></div>
                        </a>
                    </li>
                <?php $tab_idx++; endforeach; ?>
            </ul>

            <div class="bg-dark p-2 mb-4 rounded border border-secondary d-flex gap-4 px-4 text-secondary small">
                <div><span class="d-inline-block rounded-circle" style="width:10px;height:10px;background:#4ade80;"></span> Available</div>
                <div><span class="d-inline-block rounded-circle" style="width:10px;height:10px;background:#fbbf24;"></span> Fast Filling</div>
            </div>

            <div class="tab-content">
                <?php $comp_idx = 0; foreach($shows_by_date as $date_key => $theaters): 
                     $is_active = ($target_date === $date_key) || (empty($target_date) && $comp_idx === 0);
                ?>
                    <div class="tab-pane fade <?= $is_active ? 'show active' : '' ?>" id="date-<?= $comp_idx ?>">
                        <?php foreach($theaters as $theater_name => $shows): ?>
                            <div class="theater-row">
                                <div class="theater-info">
                                    <h5><i class="far fa-heart mt-1 me-2 text-secondary"></i> <?= htmlspecialchars($theater_name) ?></h5>
                                    <div class="mt-2 ms-4 city"><i class="fas fa-mobile-alt me-1"></i> M-Ticket Available</div>
                                </div>
                                <div class="timings-container">
                                    <?php foreach($shows as $sh): 
                                        $is_past = ($sh['show_date'] == date('Y-m-d') && strtotime($sh['show_time']) < time()) || ($sh['show_date'] < date('Y-m-d'));
                                        
                                        $capacity = $sh['total_seats'] > 0 ? $sh['total_seats'] : 100; // prevent div by zero
                                        $filled_pct = ($sh['booked_count'] / $capacity) * 100;
                                        $is_fast_filling = ($filled_pct > 70 && !$is_past);
                                        
                                        $btn_class = 'time-btn';
                                        if($is_past) $btn_class .= ' disabled';
                                        elseif($is_fast_filling) $btn_class .= ' fast-filling';
                                    ?>
                                        <div class="time-btn-container">
                                            <?php if($is_fast_filling && !$is_past): ?>
                                                <div class="fast-fill-tag">Fast Filling</div>
                                            <?php endif; ?>
                                            
                                            <div class="<?= $btn_class ?>" <?= !$is_past ? "onclick=\"selectShow(this, {$sh['id']})\"" : "" ?> title="<?= htmlspecialchars($sh['screen_name']) ?> - <?= $sh['total_seats'] - $sh['booked_count'] ?> seats left">
                                                <?= date('h:i A', strtotime($sh['show_time'])) ?>
                                                <small><?= htmlspecialchars($sh['screen_name']) ?></small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php $comp_idx++; endforeach; ?>
            </div>

            <!-- Sticky Bottom Checkout Bar -->
            <div class="checkout-bar" id="checkoutBar">
                <div class="container">
                    <div class="row align-items-center justify-content-end">
                        <div class="col-md-4 text-end d-flex gap-3 align-items-center justify-content-end">
                            <select name="tickets" class="form-select bg-dark text-white border-secondary w-auto" required>
                                <?php for($i=1; $i<=10; $i++): ?>
                                    <option value="<?= $i ?>" <?= $i==2 ? 'selected' : '' ?>><?= $i ?> Ticket(s)</option>
                                <?php endfor; ?>
                            </select>
                            <button type="submit" class="btn btn-danger px-5 py-2 fw-bold w-50" style="background: #f84464; border-color: #f84464;">Book Seats</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
    function selectShow(element, showId) {
        if(element.classList.contains('disabled')) return;
        
        document.querySelectorAll('.time-btn').forEach(el => el.classList.remove('selected'));
        element.classList.add('selected');
        document.getElementById('show_id').value = showId;
        
        // Display Checkout Bar
        document.getElementById('checkoutBar').classList.add('active');
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
