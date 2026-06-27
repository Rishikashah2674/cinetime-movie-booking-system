<?php
require_once 'includes/auth.php';

$msg = '';
$error = '';

// Handle Delete Movie
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM movies WHERE movie_id = ?");
    $stmt->bind_param("i", $del_id);
    if ($stmt->execute()) {
        $msg = "Movie deleted successfully!";
    } else {
        $error = "Failed to delete movie. It might be linked to existing bookings.";
    }
    $stmt->close();
}

// Handle Quick Inline Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'quick_update') {
    $q_m_id = intval($_POST['q_movie_id']);
    $q_price = floatval($_POST['q_price']);
    $q_dur = intval($_POST['q_duration']);
    $q_date = trim($_POST['q_release_date']);
    $q_rating = isset($_POST['q_rating']) && $_POST['q_rating'] !== '' ? floatval($_POST['q_rating']) : NULL;

    if ($q_rating !== NULL && ($q_rating < 0 || $q_rating > 10)) {
        $error = "Quick Edit Failed: Rating must be strictly between 0.0 and 10.0";
    } else {
        $stmt = $conn->prepare("UPDATE movies SET price=?, duration=?, release_date=?, rating=? WHERE movie_id=?");
        $stmt->bind_param("disdi", $q_price, $q_dur, $q_date, $q_rating, $q_m_id);
        if ($stmt->execute()) {
            $msg = "Row actively saved! Movie quick-updated successfully.";
        } else {
            $error = "Quick edit failed on database end.";
        }
    }
}

// Handle Add / Edit Movie (Full Modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $movie_id = isset($_POST['movie_id']) ? intval($_POST['movie_id']) : 0;
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $genre = trim($_POST['genre']);
    $duration = intval($_POST['duration']);
    $language = trim($_POST['language']);
    $release_date = trim($_POST['release_date']);
    $poster_url = trim($_POST['poster_url']);
    $trailer_url = trim($_POST['trailer_url']);
    $price = floatval($_POST['price']);
    $rating = isset($_POST['rating']) && $_POST['rating'] !== '' ? floatval($_POST['rating']) : NULL;
    
    if($rating !== NULL && ($rating < 0 || $rating > 10)) {
        $error = "Rating must be strictly between 0.0 and 10.0";
    }

    if (empty($error)) {
        if ($movie_id > 0) {
            // SAFE EDIT OVERWRITE LOGIC: Fetch existing first.
            $ext_q = $conn->query("SELECT * FROM movies WHERE movie_id = {$movie_id}");
            $ext = $ext_q->fetch_assoc();

            $f_title = !empty($title) ? $title : $ext['title'];
            $f_desc = !empty($description) ? $description : $ext['description'];
            $f_genre = !empty($genre) ? $genre : $ext['genre'];
            $f_dur = ($duration > 0) ? $duration : $ext['duration'];
            $f_lang = !empty($language) ? $language : $ext['language'];
            $f_date = !empty($release_date) ? $release_date : $ext['release_date'];
            $f_poster = !empty($poster_url) ? $poster_url : $ext['poster_url'];
            $f_trailer = !empty($trailer_url) ? $trailer_url : $ext['trailer_url'];
            $f_price = ($price > 0) ? $price : $ext['price'];
            
            $stmt = $conn->prepare("UPDATE movies SET title=?, description=?, genre=?, duration=?, release_date=?, language=?, poster_url=?, trailer_url=?, price=?, rating=? WHERE movie_id=?");
            $stmt->bind_param("sssisssssdi", $f_title, $f_desc, $f_genre, $f_dur, $f_date, $f_lang, $f_poster, $f_trailer, $f_price, $rating, $movie_id);
            if ($stmt->execute()) {
                $msg = "Movie safely updated securely relying only on modified fields!";
            } else {
                $error = "Failed to update movie.";
            }
        } else {
            // Insert
            if (empty($title) || empty($genre)) {
                $error = "Title and Genre are absolutely required for a completely new movie.";
            } else {
                $stmt = $conn->prepare("INSERT INTO movies (title, description, genre, duration, release_date, language, poster_url, trailer_url, price, rating) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssisssssd", $title, $description, $genre, $duration, $release_date, $language, $poster_url, $trailer_url, $price, $rating);
                if ($stmt->execute()) {
                    $msg = "New movie added successfully!";
                } else {
                    $error = "Failed to add movie.";
                }
            }
        }
        if(isset($stmt)) $stmt->close();
    } else {
        $error = "Title and Genre are required.";
    }
}

require_once 'includes/header.php';

// Fetch all movies
$movies = $conn->query("SELECT * FROM movies ORDER BY created_at DESC");
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Manage Movies</span>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#movieModal" onclick="clearModal()">
            <i class="fas fa-plus"></i> Add New Movie
        </button>
    </div>
    <div class="card-body">
        <?php if($msg): ?> <div class="alert alert-success"><?= $msg ?></div> <?php endif; ?>
        <?php if($error): ?> <div class="alert alert-danger"><?= $error ?></div> <?php endif; ?>

        <div class="table-responsive">
            <!-- Inline Edit Form wrapper over the entire table -->
            <form method="POST" id="quickEditForm">
                <input type="hidden" name="action" value="quick_update">
                <input type="hidden" name="q_movie_id" id="target_quick_id" value="">
                
                <table class="table table-hover table-striped align-middle">
                    <thead>
                        <tr>
                            <th width="80">Poster</th>
                            <th>Movie Details</th>
                            <th>Base Price</th>
                            <th>Release Date</th>
                            <th width="150" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($movies && $movies->num_rows > 0): ?>
                            <?php while($m = $movies->fetch_assoc()): ?>
                            <tr id="row-<?= $m['movie_id'] ?>" style="transition: background 0.3s;">
                                <td>
                                    <img src="<?= htmlspecialchars($m['poster_url']) ?>" alt="Poster" style="width: 50px; height: 75px; object-fit: cover; border-radius: 4px;">
                                </td>
                                <td>
                                    <div class="fw-bold text-light"><?= htmlspecialchars($m['title']) ?></div>
                                    <div class="text-secondary small">
                                        <span class="badge bg-secondary"><?= htmlspecialchars($m['language']) ?></span>
                                        <span class="badge bg-dark"><?= htmlspecialchars($m['genre']) ?></span>
                                        
                                        <!-- Read Mode Data -->
                                        <span class="read-block-<?= $m['movie_id'] ?>">
                                            <span class="ms-1"><?= $m['duration'] ?> mins</span>
                                            <?php if(!is_null($m['rating'])): ?>
                                                <span class="text-warning ms-2"><i class="fas fa-star"></i> <?= number_format($m['rating'], 1) ?></span>
                                            <?php else: ?>
                                                <span class="text-secondary ms-2"><i class="fas fa-star"></i> N/A</span>
                                            <?php endif; ?>
                                        </span>
                                        
                                        <!-- Edit Mode Inputs -->
                                        <div class="edit-block-<?= $m['movie_id'] ?> d-none mt-2 d-flex gap-2">
                                            <input type="number" name="q_duration" class="form-control form-control-sm border-warning bg-dark text-warning w-auto" placeholder="Mins" style="max-width: 80px;" disabled>
                                            <input type="number" step="0.1" name="q_rating" class="form-control form-control-sm border-warning bg-dark text-warning w-auto" placeholder="Rating 0-10" style="max-width: 100px;" disabled>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-success fw-bold">
                                    <span class="read-block-<?= $m['movie_id'] ?>">₹<?= number_format($m['price'], 2) ?></span>
                                    <div class="edit-block-<?= $m['movie_id'] ?> d-none">
                                        <input type="number" step="0.01" name="q_price" class="form-control form-control-sm border-warning bg-dark text-warning w-auto" style="max-width: 100px;" disabled>
                                    </div>
                                </td>
                                <td>
                                    <span class="read-block-<?= $m['movie_id'] ?>"><?= date('d M Y', strtotime($m['release_date'])) ?></span>
                                    <div class="edit-block-<?= $m['movie_id'] ?> d-none">
                                        <input type="date" name="q_release_date" class="form-control form-control-sm border-warning bg-dark text-warning" disabled>
                                    </div>
                                </td>
                                <td>
                                    <!-- Read Actions -->
                                    <div class="read-block-<?= $m['movie_id'] ?> text-center">
                                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                            onclick='activateQuickEdit(<?= htmlspecialchars(json_encode($m), ENT_QUOTES, "UTF-8") ?>)' title="Inline Quick Edit">
                                            <i class="fas fa-bolt"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                            onclick='editMovie(<?= htmlspecialchars(json_encode($m), ENT_QUOTES, "UTF-8") ?>)' 
                                            data-bs-toggle="modal" data-bs-target="#movieModal" title="Full Form Edit">
                                            <i class="fas fa-expand-arrows-alt"></i>
                                        </button>
                                        <a href="?delete=<?= $m['movie_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this movie?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                    
                                    <!-- Edit Actions -->
                                    <div class="edit-block-<?= $m['movie_id'] ?> d-none d-flex gap-1 justify-content-center">
                                        <button type="submit" class="btn btn-sm btn-success fw-bold"><i class="fas fa-save"></i></button>
                                        <button type="button" class="btn btn-sm btn-secondary" onclick="cancelQuickEdit(<?= $m['movie_id'] ?>)"><i class="fas fa-times"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4">No movies in database.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </form>
        </div>
    </div>
</div>

<!-- Add/Edit Movie Modal -->
<div class="modal fade" id="movieModal" tabindex="-1" data-bs-theme="dark">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Movie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 small" id="editNotice" style="display: none;">
                        <i class="fas fa-info-circle"></i> Selective Editing Active: Any field left untouched will safely retain its old DB value. Changed inputs will highlight automatically!
                    </div>
                    <input type="hidden" name="movie_id" id="m_id" value="0">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" id="m_title" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Base Price (₹)</label>
                            <input type="number" step="0.01" name="price" id="m_price" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Genre</label>
                            <input type="text" name="genre" id="m_genre" class="form-control" placeholder="Action, Sci-Fi" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Language</label>
                            <input type="text" name="language" id="m_language" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Rating (0-10)</label>
                            <input type="number" step="0.1" max="10" min="0" name="rating" id="m_rating" class="form-control" placeholder="e.g. 8.5">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Duration (mins)</label>
                            <input type="number" name="duration" id="m_duration" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="m_description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Poster URL</label>
                            <input type="text" name="poster_url" id="m_poster" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Trailer URL</label>
                            <input type="text" name="trailer_url" id="m_trailer" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Release Date</label>
                            <input type="date" name="release_date" id="m_release" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Movie</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Highlight UX tracking
    const editInputs = document.querySelectorAll('#movieModal input, #movieModal textarea');
    editInputs.forEach(input => {
        input.addEventListener('input', function() {
            if(document.getElementById('m_id').value !== '0') {
                this.classList.add('border', 'border-warning', 'text-warning');
            }
        });
    });

    function clearModal() {
        document.getElementById('modalTitle').innerText = 'Add New Movie';
        document.getElementById('editNotice').style.display = 'none';
        document.getElementById('m_id').value = '0';
        document.getElementById('m_title').value = '';
        document.getElementById('m_price').value = '100.00';
        document.getElementById('m_genre').value = '';
        document.getElementById('m_language').value = '';
        document.getElementById('m_rating').value = '';
        document.getElementById('m_duration').value = '';
        document.getElementById('m_description').value = '';
        document.getElementById('m_poster').value = '';
        document.getElementById('m_trailer').value = '';
        document.getElementById('m_release').value = '';
        
        editInputs.forEach(input => input.classList.remove('border', 'border-warning', 'text-warning'));
    }

    function editMovie(movie) {
        document.getElementById('modalTitle').innerText = 'Edit Movie';
        document.getElementById('editNotice').style.display = 'block';
        document.getElementById('m_id').value = movie.movie_id;
        document.getElementById('m_title').value = movie.title;
        document.getElementById('m_price').value = movie.price;
        document.getElementById('m_genre').value = movie.genre;
        document.getElementById('m_language').value = movie.language;
        document.getElementById('m_rating').value = movie.rating;
        document.getElementById('m_duration').value = movie.duration;
        document.getElementById('m_description').value = movie.description;
        document.getElementById('m_poster').value = movie.poster_url;
        document.getElementById('m_trailer').value = movie.trailer_url;
        document.getElementById('m_release').value = movie.release_date;
        
        editInputs.forEach(input => input.classList.remove('border', 'border-warning', 'text-warning'));
    }

    // Inline Quick Edit Architecture
    let currentEditRow = null;

    function activateQuickEdit(movie) {
        // Cancel any active row first to prevent overlapping submits
        if(currentEditRow !== null) {
            cancelQuickEdit(currentEditRow);
        }

        const m_id = movie.movie_id;
        currentEditRow = m_id;
        
        // Setup hidden tracking ID
        document.getElementById('target_quick_id').value = m_id;

        // Toggle visibility classes natively
        document.querySelectorAll(`.read-block-${m_id}`).forEach(el => el.classList.add('d-none'));
        document.querySelectorAll(`.edit-block-${m_id}`).forEach(el => el.classList.remove('d-none'));
        
        // Highlight active row uniquely
        document.getElementById(`row-${m_id}`).style.background = 'rgba(240, 173, 78, 0.1)';

        // Route values exactly and globally enable the disabled tags!
        const editBlock = document.querySelectorAll(`.edit-block-${m_id} input`);
        editBlock.forEach(input => {
            input.disabled = false; // Enable for specific row
            
            // Re-populate native exact values
            if(input.name === 'q_duration') input.value = movie.duration;
            if(input.name === 'q_rating') input.value = movie.rating;
            if(input.name === 'q_price') input.value = movie.price;
            if(input.name === 'q_release_date') input.value = movie.release_date;
        });
    }

    function cancelQuickEdit(m_id) {
        document.getElementById('target_quick_id').value = '';
        currentEditRow = null;

        // Strip background explicitly
        document.getElementById(`row-${m_id}`).style.background = 'transparent';

        // Re-hide inputs and revert disabled tags
        document.querySelectorAll(`.edit-block-${m_id} input`).forEach(input => {
            input.disabled = true; 
        });

        document.querySelectorAll(`.read-block-${m_id}`).forEach(el => el.classList.remove('d-none'));
        document.querySelectorAll(`.edit-block-${m_id}`).forEach(el => el.classList.add('d-none'));
    }

</script>

<?php require_once 'includes/footer.php'; ?>
