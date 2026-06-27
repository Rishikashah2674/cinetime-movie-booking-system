<?php
require_once 'includes/auth.php';

$msg = '';
$error = '';

if (!isset($_GET['screen_id']) || !is_numeric($_GET['screen_id'])) {
    header("Location: theaters.php");
    exit;
}

$screen_id = intval($_GET['screen_id']);

// Fetch Screen Info
$stmt = $conn->prepare("SELECT s.*, t.name as theater_name, t.city FROM screens s JOIN theaters t ON s.theater_id = t.id WHERE s.id = ?");
$stmt->bind_param("i", $screen_id);
$stmt->execute();
$screen = $stmt->get_result()->fetch_assoc();
if (!$screen) { header("Location: theaters.php"); exit; }

// Generate Seats Logic (Bulk Support)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'bulk_generate') {
    $start_row = trim(strtoupper($_POST['start_row']));
    $end_row = trim(strtoupper($_POST['end_row']));
    $seat_count = intval($_POST['seat_count']);
    $seat_type = trim($_POST['seat_type']); 
    
    $start_char = substr($start_row, 0, 1);
    $end_char = substr($end_row, 0, 1);
    
    if (in_array($seat_type, ['Regular', 'Premium', 'Recliner']) && $seat_count > 0 && ord($start_char) <= ord($end_char)) {
        $stmt = $conn->prepare("INSERT IGNORE INTO seats (screen_id, row_name, seat_number, seat_type) VALUES (?, ?, ?, ?)");
        
        $conn->begin_transaction();
        $added = 0;
        try {
            for ($char = ord($start_char); $char <= ord($end_char); $char++) {
                $r_name = chr($char);
                for ($i = 1; $i <= $seat_count; $i++) {
                    $stmt->bind_param("isis", $screen_id, $r_name, $i, $seat_type);
                    $stmt->execute();
                    if ($stmt->affected_rows > 0) $added++;
                }
            }
            
            $conn->query("UPDATE screens SET total_seats = (SELECT COUNT(*) FROM seats WHERE screen_id = {$screen_id}) WHERE id = {$screen_id}");
            $conn->commit();
            $msg = "Generated {$added} new seats seamlessly matching rows {$start_char} through {$end_char}!";
        } catch(Exception $e) {
            $conn->rollback();
            $error = "Failed to bulk generate layout blocks.";
        }
    } else {
        $error = "Invalid range constraint: End row must mathematically fall at or after start row (e.g. A to D).";
    }
}

// Delete Row Logic
if (isset($_GET['delete_row'])) {
    $r_name = $_GET['delete_row'];
    $stmt = $conn->prepare("DELETE FROM seats WHERE screen_id = ? AND row_name = ?");
    $stmt->bind_param("is", $screen_id, $r_name);
    $stmt->execute();
    $conn->query("UPDATE screens SET total_seats = (SELECT COUNT(*) FROM seats WHERE screen_id = {$screen_id}) WHERE id = {$screen_id}");
    header("Location: seat_maps.php?screen_id={$screen_id}");
    exit;
}

// Wipe All Logic
if (isset($_GET['wipe_all'])) {
    $conn->query("DELETE FROM seats WHERE screen_id = {$screen_id}");
    $conn->query("UPDATE screens SET total_seats = 0 WHERE id = {$screen_id}");
    header("Location: seat_maps.php?screen_id={$screen_id}");
    exit;
}

require_once 'includes/header.php';

// Fetch Current Map
$seats_res = $conn->query("SELECT * FROM seats WHERE screen_id = {$screen_id} ORDER BY row_name, seat_number");
$seat_map = [];
while ($s = $seats_res->fetch_assoc()) {
    $seat_map[$s['row_name']][] = $s;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="theaters.php" class="btn btn-sm btn-outline-secondary mb-2"><i class="fas fa-arrow-left"></i> Back to Theaters</a>
        <h2 class="text-white mb-0">Seat Map Builder</h2>
        <p class="text-info"><?= htmlspecialchars($screen['theater_name']) ?> (<?= htmlspecialchars($screen['city']) ?>) &raquo; <?= htmlspecialchars($screen['screen_name']) ?></p>
    </div>
</div>

<?php if($msg): ?> <div class="alert alert-success border-0 shadow-sm"><i class="fas fa-check-circle me-2"></i> <?= $msg ?></div> <?php endif; ?>
<?php if($error): ?> <div class="alert alert-danger border-0 shadow-sm"><i class="fas fa-exclamation-circle me-2"></i> <?= $error ?></div> <?php endif; ?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card bg-dark">
            <div class="card-header bg-danger text-white"><i class="fas fa-layer-group me-2"></i> Bulk Structure Generator</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="bulk_generate">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Start Row (e.g. A)</label>
                            <input type="text" name="start_row" class="form-control text-uppercase" maxlength="1" required placeholder="A">
                        </div>
                        <div class="col-6">
                            <label class="form-label">End Row (e.g. E)</label>
                            <input type="text" name="end_row" class="form-control text-uppercase" maxlength="1" required placeholder="E">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Number of Seats (Per Row)</label>
                        <input type="number" name="seat_count" class="form-control" min="1" max="100" required placeholder="15">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Seat Class Type</label>
                        <select name="seat_type" class="form-select" required>
                            <option value="Regular">Regular</option>
                            <option value="Premium">Premium</option>
                            <option value="Recliner">Recliner</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-danger w-100 fw-bold"><i class="fas fa-bolt me-1"></i> Generate Block</button>
                    <div class="form-text text-secondary mt-2">New rows will be seamlessly forcefully generated appending exactly into the visual layout bounds. Pre-existing seats won't be overwritten.</div>
                </form>
            </div>
            <div class="card-footer bg-dark border-secondary text-center">
                <a href="?screen_id=<?= $screen_id ?>&wipe_all=true" class="btn btn-sm btn-outline-danger w-100" onclick="return confirm('WARNING: Are you absolutely sure you want to completely erase the entire layout from the database?');">
                    <i class="fas fa-trash-alt me-1"></i> Wipe the whole Layout
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card bg-dark h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-th me-2"></i> Visual Seat Map</span>
                <span class="badge bg-danger">Total Capacity: <?= $screen['total_seats'] ?></span>
            </div>
            <div class="card-body text-center overflow-auto">
                <div class="bg-secondary text-white fw-bold py-1 mb-4 rounded w-75 mx-auto" style="box-shadow: 0 5px 15px rgba(255,255,255,0.1);">SCREEN THIS WAY</div>
                
                <?php if (empty($seat_map)): ?>
                    <div class="text-secondary p-5">No seats generated for this screen yet.</div>
                <?php else: ?>
                    <div class="d-inline-flex flex-column align-items-center gap-2">
                        <?php foreach($seat_map as $row_name => $seats): ?>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold text-info" style="width:25px;"><?= $row_name ?></span>
                                <div class="d-flex gap-1 flex-wrap justify-content-center">
                                    <?php foreach($seats as $s): ?>
                                        <?php 
                                            // Color code by type
                                            $color = '#444'; // Regular
                                            if($s['seat_type'] == 'Premium') $color = '#7952b3';
                                            if($s['seat_type'] == 'Recliner') $color = '#e50914';
                                        ?>
                                        <div class="seat-badge" title="<?= $row_name.$s['seat_number'] ?> - <?= $s['seat_type'] ?>" style="background-color: <?= $color ?>;">
                                            <?= $s['seat_number'] ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <a href="?screen_id=<?= $screen_id ?>&delete_row=<?= $row_name ?>" class="btn btn-sm btn-link text-danger ms-2" onclick="return confirm('Delete entirely row <?= $row_name ?>?');"><i class="fas fa-times"></i></a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Legend -->
                    <div class="mt-5 d-flex justify-content-center gap-4">
                        <div><span class="d-inline-block rounded" style="width:15px;height:15px;background:#444;"></span> Regular</div>
                        <div><span class="d-inline-block rounded" style="width:15px;height:15px;background:#7952b3;"></span> Premium</div>
                        <div><span class="d-inline-block rounded" style="width:15px;height:15px;background:#e50914;"></span> Recliner</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.seat-badge {
    width: 30px; height: 30px;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 11px; font-weight: bold;
    border-radius: 6px; cursor: default;
    border: 1px solid rgba(255,255,255,0.1);
}
</style>
<?php require_once 'includes/footer.php'; ?>
