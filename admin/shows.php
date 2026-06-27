<?php
require_once 'includes/auth.php';

$msg = '';
$error = '';

// Handle Delete Show
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM shows WHERE id = ?");
    $stmt->bind_param("i", $del_id);
    if ($stmt->execute()) {
        $msg = "Showtime deleted successfully!";
    } else {
        $error = "Failed to delete showtime.";
    }
    $stmt->close();
}

// Handle Add Show
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_show') {
    $movie_id = intval($_POST['movie_id']);
    $theater_id = intval($_POST['theater_id']); 
    $screen_id = intval($_POST['screen_id']);
    $show_date = trim($_POST['show_date']);
    // Accept multiple times as string (e.g., 10:00, 14:00, 18:30)
    $show_times_raw = trim($_POST['show_times']); 
    $show_times = array_map('trim', explode(',', $show_times_raw));
    
    // Dynamic Pricing handling
    $price_reg = floatval($_POST['price_regular']);
    $price_prem = floatval($_POST['price_premium']);
    $price_rec = floatval($_POST['price_recliner']);

    if ($movie_id > 0 && $screen_id > 0 && !empty($show_date) && !empty($show_times)) {
        $conn->begin_transaction();
        try {
            $inserted_count = 0;
            // Iterate over all provided times
            foreach ($show_times as $single_time) {
                if (empty($single_time)) continue;
                
                // Formulate valid time
                $parsed_time = date('H:i:s', strtotime($single_time));
                
                // Insert Show
                $stmt = $conn->prepare("INSERT INTO shows (movie_id, theater_id, screen_id, show_date, show_time, base_price) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iiissd", $movie_id, $theater_id, $screen_id, $show_date, $parsed_time, $price_reg);
                $stmt->execute();
                $new_show_id = $conn->insert_id;
                
                // Insert Seat Pricing
                $stmt_price = $conn->prepare("INSERT INTO seat_pricing (show_id, seat_type, price) VALUES (?, ?, ?)");
                
                $types = ['Regular' => $price_reg, 'Premium' => $price_prem, 'Recliner' => $price_rec];
                foreach($types as $type => $pr) {
                    if ($pr > 0) {
                        $stmt_price->bind_param("isd", $new_show_id, $type, $pr);
                        $stmt_price->execute();
                    }
                }
                $inserted_count++;
            }
            
            $conn->commit();
            $msg = "{$inserted_count} new show timings configured successfully!";
        } catch(Exception $e) {
            $conn->rollback();
            $error = "Failed to create shows. Data conflict or invalid format.";
        }
    } else {
        $error = "Please fill all required scheduling fields.";
    }
}

require_once 'includes/header.php';

// Fetch options for Selects
$movies = $conn->query("SELECT movie_id, title FROM movies");
$theaters_res = $conn->query("SELECT t.id as t_id, t.name as t_name, s.id as s_id, s.screen_name FROM screens s JOIN theaters t ON s.theater_id = t.id ORDER BY t.name");
$screens_options = [];
while ($row = $theaters_res->fetch_assoc()) {
    $screens_options[] = $row;
}

// Fetch all shows
$shows = $conn->query("
    SELECT sh.*, m.title as movie_title, t.name as theater_name, t.city, s.screen_name 
    FROM shows sh
    JOIN movies m ON sh.movie_id = m.movie_id
    JOIN theaters t ON sh.theater_id = t.id
    JOIN screens s ON sh.screen_id = s.id
    ORDER BY sh.show_date DESC, sh.show_time DESC
");
?>

<div class="card bg-dark">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-calendar-alt text-warning me-2"></i> Schedule Shows & Define Pricing</span>
        <button class="btn btn-sm btn-warning text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#showModal">
            <i class="fas fa-plus"></i> Schedule New Show
        </button>
    </div>
    <div class="card-body">
        <?php if($msg): ?> <div class="alert alert-success border-0 shadow-sm"><i class="fas fa-check-circle me-2"></i> <?= $msg ?></div> <?php endif; ?>
        <?php if($error): ?> <div class="alert alert-danger border-0 shadow-sm"><i class="fas fa-exclamation-circle me-2"></i> <?= $error ?></div> <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle">
                <thead>
                    <tr>
                        <th>Movie</th>
                        <th>Venue Location</th>
                        <th>Schedule</th>
                        <th>Base Price (Reg)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($shows && $shows->num_rows > 0): ?>
                        <?php while($sh = $shows->fetch_assoc()): ?>
                        <tr>
                            <td><div class="fw-bold text-light"><?= htmlspecialchars($sh['movie_title']) ?></div></td>
                            <td>
                                <i class="fas fa-building text-secondary"></i> <?= htmlspecialchars($sh['theater_name']) ?> (<?= htmlspecialchars($sh['city']) ?>)<br>
                                <span class="badge bg-dark border border-secondary mt-1"><i class="fas fa-tv text-info"></i> <?= htmlspecialchars($sh['screen_name']) ?></span>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= htmlspecialchars($sh['show_date']) ?></span>
                                <span class="badge bg-warning text-dark"><?= htmlspecialchars($sh['show_time']) ?></span>
                            </td>
                            <td class="text-success fw-bold">₹<?= number_format($sh['base_price'], 2) ?></td>
                            <td>
                                <a href="?delete=<?= $sh['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this show schedule? Bookings will be orphaned.');">
                                    <i class="fas fa-trash"></i> Drop
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-4 text-secondary">No shows currently scheduled in the system.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Show Modal -->
<div class="modal fade" id="showModal" tabindex="-1" data-bs-theme="dark">
    <div class="modal-dialog modal-lg">
        <form method="POST">
            <input type="hidden" name="action" value="add_show">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-warning"><i class="fas fa-calendar-plus me-2"></i> Schedule Movie Timeline</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <h6 class="text-secondary border-bottom border-secondary pb-2 mb-3">1. Select Event Context</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Attached Movie</label>
                            <select name="movie_id" class="form-select" required>
                                <option value="">-- Choose Movie --</option>
                                <?php while($m = $movies->fetch_assoc()): ?>
                                    <option value="<?= $m['movie_id'] ?>"><?= htmlspecialchars($m['title']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Target Screen Location</label>
                            <select name="screen_id" id="screen_id" class="form-select" required onchange="updateTheaterId()">
                                <option value="">-- Choose Screen --</option>
                                <?php foreach($screens_options as $sc): ?>
                                    <option value="<?= $sc['s_id'] ?>" data-tid="<?= $sc['t_id'] ?>">
                                        <?= htmlspecialchars($sc['t_name']) ?> &raquo; <?= htmlspecialchars($sc['screen_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="theater_id" id="theater_id" value="">
                        </div>
                    </div>
                    
                    <h6 class="text-secondary border-bottom border-secondary pb-2 mb-3">2. Set Timeline</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Show Date</label>
                            <input type="date" name="show_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Multiple Show Times (Comma separated)</label>
                            <input type="text" name="show_times" class="form-control" placeholder="e.g. 10:00, 13:30, 18:00, 21:30" required>
                            <div class="form-text text-secondary mt-1">Insert multiple timings for this date mapped automatically. Use 24h or simple formats.</div>
                        </div>
                    </div>
                    
                    <h6 class="text-secondary border-bottom border-secondary pb-2 mb-3">3. Define Dynamic Pricing Matrix</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-light">Regular Tier (₹)</label>
                            <input type="number" step="0.01" name="price_regular" class="form-control border-secondary" value="150" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="color:#b388ff;">Premium Tier (₹)</label>
                            <input type="number" step="0.01" name="price_premium" class="form-control border-primary" value="250" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-danger">Recliner Tier (₹)</label>
                            <input type="number" step="0.01" name="price_recliner" class="form-control border-danger" value="400" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning text-dark w-100 fw-bold"><i class="fas fa-rocket me-1"></i> Deploy Schedule into Live Environment</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function updateTheaterId() {
    var sel = document.getElementById('screen_id');
    var opt = sel.options[sel.selectedIndex];
    var tid = opt.getAttribute('data-tid');
    document.getElementById('theater_id').value = tid ? tid : '';
}
</script>

<?php require_once 'includes/footer.php'; ?>
