<?php
require_once 'includes/auth.php';
require_once 'includes/header.php';

// Fetch all bookings using relational Cinema Architecture
$bookings = $conn->query("
    SELECT b.booking_id, b.show_id, b.tickets, b.seats, 
           b.total_price, b.payment_status, b.booked_at,
           u.name as user_name, u.email,
           m.title as movie_title,
           t.name as theater_name, t.city,
           s.screen_name,
           sh.show_date, sh.show_time
    FROM bookings b
    LEFT JOIN user u ON b.user_id = u.user_id
    LEFT JOIN shows sh ON b.show_id = sh.id
    LEFT JOIN movies m ON sh.movie_id = m.movie_id
    LEFT JOIN theaters t ON sh.theater_id = t.id
    LEFT JOIN screens s ON sh.screen_id = s.id
    ORDER BY b.booked_at DESC
");
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>All Bookings</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>User</th>
                        <th>Movie & Theater</th>
                        <th>Show Schedule</th>
                        <th>Seats (Qty)</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($bookings && $bookings->num_rows > 0): ?>
                        <?php while($row = $bookings->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $row['booking_id'] ?></td>
                            <td>
                                <div><?= htmlspecialchars($row['user_name'] ?? 'Guest') ?></div>
                                <div class="text-secondary small"><?= htmlspecialchars($row['email'] ?? '') ?></div>
                            </td>
                            <td>
                                <div class="fw-bold text-light"><?= htmlspecialchars($row['movie_title'] ?? 'Unknown Movie') ?></div>
                                <?php if($row['theater_name']): ?>
                                    <div class="text-secondary small"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($row['theater_name']) ?> (<?= htmlspecialchars($row['city']) ?>)</div>
                                    <div class="text-info small"><i class="fas fa-tv"></i> <?= htmlspecialchars($row['screen_name']) ?></div>
                                <?php else: ?>
                                    <div class="text-secondary small">Legacy / Orphaned Data</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($row['show_date']): ?>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($row['show_date']) ?></span><br>
                                    <span class="badge bg-warning text-dark mt-1"><?= htmlspecialchars($row['show_time']) ?></span>
                                <?php else: ?>
                                    <span class="text-secondary">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><span class="badge border border-secondary text-light"><?= htmlspecialchars($row['seats']) ?></span></div>
                                <div class="text-secondary small mt-1">(<?= $row['tickets'] ?> Tickets)</div>
                            </td>
                            <td class="text-success fw-bold">₹<?= number_format($row['total_price'], 2) ?></td>
                            <td>
                                <?php if($row['payment_status'] == 'Confirmed' || $row['payment_status'] == 'Success'): ?>
                                    <span class="badge bg-success">Confirmed</span>
                                <?php elseif($row['payment_status'] == 'Pending'): ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php else: ?>
                                    <span class="badge bg-danger"><?= htmlspecialchars($row['payment_status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-secondary small"><?= date('d M Y', strtotime($row['booked_at'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center py-4 text-secondary">No bookings found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
