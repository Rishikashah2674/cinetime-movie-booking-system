<?php
require_once 'includes/auth.php';

$msg = '';
$error = '';
$gujarat_cities = ['Ahmedabad', 'Gandhinagar', 'Surat', 'Vadodara', 'Rajkot'];

// Handlers for Theater operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_theater') {
    $name = trim($_POST['name']);
    $city = trim($_POST['city']);
    $address = trim($_POST['address']);

    if (!empty($name) && in_array($city, $gujarat_cities)) {
        $stmt = $conn->prepare("INSERT INTO theaters (name, city, address) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $city, $address);
        if ($stmt->execute()) {
            $msg = "Theater '{$name}' added successfully in {$city}!";
        } else {
            $error = "Failed to add theater.";
        }
    } else {
        $error = "Please provide valid inputs and select a Gujarat city.";
    }
}

if (isset($_GET['delete_theater']) && is_numeric($_GET['delete_theater'])) {
    $del_id = intval($_GET['delete_theater']);
    $stmt = $conn->prepare("DELETE FROM theaters WHERE id = ?");
    $stmt->bind_param("i", $del_id);
    if ($stmt->execute()) {
        $msg = "Theater deleted successfully.";
    } else {
        $error = "Failed to delete theater. Ensure all linked screens and shows are removed first.";
    }
}

// Handlers for Screen operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_screen') {
    $theater_id = intval($_POST['theater_id']);
    $screen_name = trim($_POST['screen_name']);
    
    if ($theater_id > 0 && !empty($screen_name)) {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO screens (theater_id, screen_name, total_seats) VALUES (?, ?, 0)");
            $stmt->bind_param("is", $theater_id, $screen_name);
            $stmt->execute();
            $new_screen_id = $conn->insert_id;
            
            // Auto generate default seats grid
            $seat_insert = $conn->prepare("INSERT INTO seats (screen_id, row_name, seat_number, seat_type) VALUES (?, ?, ?, ?)");
            $total_added = 0;
            
            $blueprint = [
                ['rows' => ['A','B','C','D','E'], 'count' => 15, 'type' => 'Regular'],
                ['rows' => ['F','G','H','I','J'], 'count' => 18, 'type' => 'Premium'],
                ['rows' => ['K','L','M','N','O'], 'count' => 20, 'type' => 'Recliner'],
            ];
            
            foreach($blueprint as $block) {
                foreach($block['rows'] as $r) {
                    for ($i = 1; $i <= $block['count']; $i++) {
                        $seat_insert->bind_param("isis", $new_screen_id, $r, $i, $block['type']);
                        $seat_insert->execute();
                        $total_added++;
                    }
                }
            }
            
            $conn->query("UPDATE screens SET total_seats = {$total_added} WHERE id = {$new_screen_id}");
            $conn->commit();
            $msg = "Screen '{$screen_name}' created successfully with auto-generated {$total_added} default seat layout!";
        } catch(Exception $e) {
            $conn->rollback();
            $error = "Failed to add screen and default setup.";
        }
    }
}

if (isset($_GET['delete_screen']) && is_numeric($_GET['delete_screen'])) {
    $del_id = intval($_GET['delete_screen']);
    $stmt = $conn->prepare("DELETE FROM screens WHERE id = ?");
    $stmt->bind_param("i", $del_id);
    if ($stmt->execute()) {
        $msg = "Screen deleted successfully.";
    }
}

require_once 'includes/header.php';

// Fetch Data
$theaters_res = $conn->query("SELECT * FROM theaters ORDER BY city, name");
$theaters = [];
while ($t = $theaters_res->fetch_assoc()) {
    $theaters[$t['id']] = $t;
    $theaters[$t['id']]['screens'] = [];
}

$screens_res = $conn->query("SELECT * FROM screens ORDER BY theater_id, screen_name");
while ($s = $screens_res->fetch_assoc()) {
    if (isset($theaters[$s['theater_id']])) {
        $theaters[$s['theater_id']]['screens'][] = $s;
    }
}
?>

<?php if($msg): ?> <div class="alert alert-success border-0 shadow-sm"><i class="fas fa-check-circle me-2"></i> <?= $msg ?></div> <?php endif; ?>
<?php if($error): ?> <div class="alert alert-danger border-0 shadow-sm"><i class="fas fa-exclamation-circle me-2"></i> <?= $error ?></div> <?php endif; ?>

<div class="row g-4">
    <!-- Add Theater Form -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><i class="fas fa-building me-2 text-danger"></i> Register Theater</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add_theater">
                    <div class="mb-3">
                        <label class="form-label">Theater Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. PVR Acropolis" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gujarat City</label>
                        <select name="city" class="form-select" required>
                            <option value="">-- Select City --</option>
                            <?php foreach($gujarat_cities as $c): ?>
                                <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="S.G. Highway" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger w-100"><i class="fas fa-plus me-1"></i> Add Theater</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Screen Form -->
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-tv me-2 text-info"></i> Manage Theaters & Screens</span>
                <button class="btn btn-sm btn-info text-dark" data-bs-toggle="modal" data-bs-target="#addScreenModal"><i class="fas fa-plus"></i> Add Screen</button>
            </div>
            <div class="card-body p-0">
                <div class="accordion accordion-flush" id="theaterAccordion" style="border-radius: 12px; overflow: hidden;">
                    <?php if (empty($theaters)): ?>
                        <div class="p-4 text-center text-secondary">No theaters registered yet.</div>
                    <?php endif; ?>

                    <?php foreach ($theaters as $t): ?>
                    <div class="accordion-item bg-transparent border-bottom border-secondary">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-dark text-white" type="button" data-bs-toggle="collapse" data-bs-target="#theater-<?= $t['id'] ?>">
                                <strong><?= htmlspecialchars($t['name']) ?></strong> 
                                <span class="badge bg-secondary ms-3"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($t['city']) ?></span>
                            </button>
                        </h2>
                        <div id="theater-<?= $t['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#theaterAccordion">
                            <div class="accordion-body bg-transparent">
                                <div class="d-flex justify-content-between mb-3 border-bottom border-secondary pb-3">
                                    <div class="small text-secondary"><i class="fas fa-location-arrow"></i> <?= htmlspecialchars($t['address']) ?></div>
                                    <a href="?delete_theater=<?= $t['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this theater entirely?');"><i class="fas fa-trash"></i> Delete Theater</a>
                                </div>
                                
                                <h6 class="text-info mb-3">Linked Screens:</h6>
                                <?php if(empty($t['screens'])): ?>
                                    <div class="text-secondary small">No screens configured for this location yet.</div>
                                <?php else: ?>
                                    <div class="row g-3">
                                        <?php foreach($t['screens'] as $s): ?>
                                            <div class="col-md-4">
                                                <div class="border border-secondary rounded p-3 text-center position-relative">
                                                    <h5 class="mb-1 text-light"><i class="fas fa-desktop text-primary"></i> <?= htmlspecialchars($s['screen_name']) ?></h5>
                                                    <span class="small text-secondary">Capacity: <?= $s['total_seats'] ?> seats</span>
                                                    
                                                    <div class="mt-3 btn-group w-100">
                                                        <a href="seat_maps.php?screen_id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-success border-secondary"><i class="fas fa-th"></i> Edit Map</a>
                                                        <a href="?delete_screen=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger border-secondary" onclick="return confirm('Delete this screen and its entire seat map?');"><i class="fas fa-trash"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Screen Modal -->
<div class="modal fade" id="addScreenModal" tabindex="-1" data-bs-theme="dark">
    <div class="modal-dialog">
        <form method="POST">
            <input type="hidden" name="action" value="add_screen">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Register Screen explicitly</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Parent Theater</label>
                        <select name="theater_id" class="form-select" required>
                            <option value="">-- Choose Theater --</option>
                            <?php foreach($theaters as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?> (<?= htmlspecialchars($t['city']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Screen Identifier (e.g. "Screen 1" or "IMAX 3D")</label>
                        <input type="text" name="screen_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info w-100">Create Screen</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.accordion-button::after { filter: invert(1); }
</style>
<?php require_once 'includes/footer.php'; ?>
