<?php
require_once 'includes/auth.php';
require_once 'includes/header.php';

// Fetch statistics
// 1. Total Movies
$res = $conn->query("SELECT COUNT(*) as cnt FROM movies");
$total_movies = $res->fetch_assoc()['cnt'];

// 2. Total Users
$res = $conn->query("SELECT COUNT(*) as cnt FROM user");
$total_users = $res->fetch_assoc()['cnt'];

// 3. Total Bookings
$res = $conn->query("SELECT COUNT(*) as cnt FROM bookings");
$total_bookings = $res->fetch_assoc()['cnt'];

// 4. Total Revenue (Only from successful payments)
$res = $conn->query("SELECT SUM(amount) as total FROM payments WHERE status = 'Success'");
$total_revenue = $res->fetch_assoc()['total'] ?? 0;

// Fetch last 5 bookings for a quick table
$recent_bookings = $conn->query("
    SELECT b.booking_id, u.name as user_name, m.title as movie_title, b.total_price, b.payment_status 
    FROM bookings b 
    LEFT JOIN user u ON b.user_id = u.user_id
    LEFT JOIN shows sh ON b.show_id = sh.id
    LEFT JOIN movies m ON sh.movie_id = m.movie_id
    ORDER BY b.booked_at DESC LIMIT 5
");
?>
<style>
    .stat-card {
        padding: 30px;
        border-radius: 15px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: linear-gradient(145deg, #1e1e28, #17171e);
        position: relative;
        overflow: hidden;
        border: 1px solid var(--border-color);
        transition: transform 0.3s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        border-color: var(--accent-primary);
    }
    .stat-card i {
        font-size: 3rem;
        position: absolute;
        right: 20px;
        top: 30px;
        color: rgba(255,255,255,0.05);
    }
    .stat-title {
        font-size: 0.9rem;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--text-secondary);
        z-index: 2;
    }
    .stat-value {
        font-size: 2.2rem;
        font-weight: 800;
        margin-top: 10px;
        color: #fff;
        z-index: 2;
    }
</style>

<div class="row g-4 mb-5">
    <!-- Stat Cards -->
    <div class="col-md-3">
        <div class="stat-card">
            <i class="fas fa-film"></i>
            <div class="stat-title">Total Movies</div>
            <div class="stat-value"><?= number_format($total_movies) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <i class="fas fa-users"></i>
            <div class="stat-title">Registered Users</div>
            <div class="stat-value"><?= number_format($total_users) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <i class="fas fa-ticket-alt"></i>
            <div class="stat-title">Total Bookings</div>
            <div class="stat-value"><?= number_format($total_bookings) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <i class="fas fa-wallet"></i>
            <div class="stat-title">Total Revenue</div>
            <div class="stat-value text-success">₹<?= number_format($total_revenue, 2) ?></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Recent Bookings Overview</span>
                <a href="bookings.php" class="btn btn-sm btn-outline-light">View All</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-striped m-0">
                    <thead>
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>User</th>
                            <th>Movie</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($recent_bookings && $recent_bookings->num_rows > 0): ?>
                            <?php while($row = $recent_bookings->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4">#<?= $row['booking_id'] ?></td>
                                <td><?= htmlspecialchars($row['user_name'] ?? 'Guest') ?></td>
                                <td><?= htmlspecialchars($row['movie_title'] ?? 'Unknown') ?></td>
                                <td>₹<?= number_format($row['total_price'], 2) ?></td>
                                <td>
                                    <?php if($row['payment_status'] == 'Confirmed' || $row['payment_status'] == 'Success'): ?>
                                        <span class="badge bg-success">Confirmed</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark"><?= htmlspecialchars($row['payment_status']) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4">No recent bookings found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
