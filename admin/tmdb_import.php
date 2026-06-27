<?php
require_once 'includes/auth.php';

require_once 'config_tmdb.php';

$msg = '';
$error = '';
$tmdb_results = [];

// Handle API Fetching
function fetchNowPlayingIndia() {
    $api_key = defined('TMDB_API_KEY') ? TMDB_API_KEY : '';
    
    if (empty($api_key) || $api_key == 'YOUR_ENV_TMDB_KEY_HERE') {
        return ['error' => 'API Key missing or invalid. Please configure admin/config_tmdb.php.'];
    }

    $url = "https://api.themoviedb.org/3/movie/now_playing?api_key={$api_key}&region=IN&page=1";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Localhost XAMPP network constraints patch
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if(curl_errno($ch)){
        return ['error' => 'cURL Error: ' . curl_error($ch)];
    }
    curl_close($ch);
    
    if ($http_code == 401) {
        return ['error' => 'TMDB Authentication Failed: Invalid API Key. Check config_tmdb.php.'];
    } elseif ($http_code != 200) {
        return ['error' => 'TMDB API Exception. HTTP Status: ' . $http_code];
    }
    
    $data = json_decode($response, true);
    return ['success' => true, 'data' => $data['results']];
}

// Handle Import POST logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_tmdb_id'])) {
    $tmdb_id = intval($_POST['import_tmdb_id']);
    $title = trim($_POST['title']);
    $language = trim($_POST['language']);
    $release_date = trim($_POST['release_date']);
    $description = trim($_POST['description']);
    $poster_url = trim($_POST['poster_url']);
    $genre = 'Unknown'; // Can be mapped if needed, simple for now
    $duration = 120; // Default placeholder for now playing
    $price = 150.00;
    
    $price = 150.00;
    $rating = isset($_POST['rating']) ? floatval($_POST['rating']) : NULL;
    
    // Determine badge identity logic correctly mapped against readable POST values
    $is_indian = in_array(strtolower($language), ['hindi', 'tamil', 'telugu', 'malayalam', 'kannada', 'gujarati']) ? 1 : 0;

    // Check duplicate
    $check = $conn->prepare("SELECT movie_id FROM movies WHERE tmdb_id = ? OR title = ?");
    $check->bind_param("is", $tmdb_id, $title);
    $check->execute();
    $res = $check->get_result();
    
    if ($res->num_rows > 0) {
        $error = "Movie '{$title}' already exists in your database.";
    } else {
        $stmt = $conn->prepare("INSERT INTO movies (tmdb_id, title, description, genre, duration, release_date, language, poster_url, price, is_indian_release, trailer_url, rating) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '', ?)");
        $stmt->bind_param("isssisssdid", $tmdb_id, $title, $description, $genre, $duration, $release_date, $language, $poster_url, $price, $is_indian, $rating);
        
        if ($stmt->execute()) {
            $msg = "Successfully imported '{$title}'!";
        } else {
            $error = "Failed to import movie. DB Error.";
        }
    }
}

// Fetch Logic State
if (isset($_GET['fetch']) && $_GET['fetch'] == 'now_playing') {
    $fetch_result = fetchNowPlayingIndia();
    if (isset($fetch_result['error'])) {
        $error = $fetch_result['error'];
    } else {
        $allowed_langs = ['hi', 'ta', 'te', 'ml', 'kn', 'gu', 'en'];
        $raw_results = $fetch_result['data'];
        
        foreach ($raw_results as $movie) {
            if (in_array($movie['original_language'], $allowed_langs)) {
                $tmdb_results[] = $movie;
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="card mb-4 border-0 bg-transparent">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="text-white mb-0"><i class="fas fa-cloud-download-alt text-primary"></i> TMDB Importer</h2>
            <p class="text-secondary small mt-1">Fetch "Now Playing" movies localized strictly to India.</p>
        </div>
        <a href="?fetch=now_playing" class="btn btn-primary px-4 py-2 shadow"><i class="fas fa-sync-alt me-2"></i> Fetch Latest Movies</a>
    </div>
</div>

<?php if($msg): ?> <div class="alert alert-success border-0 shadow-sm"><i class="fas fa-check-circle me-2"></i> <?= $msg ?></div> <?php endif; ?>
<?php if($error): ?> <div class="alert alert-danger border-0 shadow-sm"><i class="fas fa-exclamation-circle me-2"></i> <?= $error ?></div> <?php endif; ?>

<?php if (!empty($tmdb_results)): ?>
    <div class="row g-4">
        <?php foreach ($tmdb_results as $m): 
            $poster = "https://image.tmdb.org/t/p/w500" . $m['poster_path'];
            $lang = $m['original_language'];
            
            // Badges logic
            $is_indian = in_array($lang, ['hi', 'ta', 'te', 'ml', 'kn', 'gu']);
            $badge_class = $is_indian ? 'bg-success' : 'bg-info text-dark';
            $badge_text = $is_indian ? '🇮🇳 Indian Release' : '🌍 English Release';
            
            // Map languages for readability
            $lang_map = ['hi'=>'Hindi', 'ta'=>'Tamil', 'te'=>'Telugu', 'ml'=>'Malayalam', 'kn'=>'Kannada', 'gu'=>'Gujarati', 'en'=>'English'];
            $display_lang = $lang_map[$lang] ?? strtoupper($lang);
        ?>
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card h-100 bg-dark border-secondary overflow-hidden shadow-lg" style="transition: transform 0.3s;">
                <img src="<?= htmlspecialchars($poster) ?>" class="card-img-top" alt="Poster" loading="lazy" style="height: 350px; object-fit: cover;">
                
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title text-light fw-bold text-truncate" title="<?= htmlspecialchars($m['title']) ?>"><?= htmlspecialchars($m['title']) ?></h5>
                    <div class="mb-3">
                        <span class="badge <?= $badge_class ?> mb-2"><?= $badge_text ?></span><br>
                        <span class="badge border border-secondary text-secondary"><i class="fas fa-language"></i> <?= $display_lang ?></span>
                        <span class="badge border border-secondary text-secondary"><i class="fas fa-calendar"></i> <?= $m['release_date'] ?></span>
                    </div>
                    
                    <form method="POST" class="mt-auto">
                        <input type="hidden" name="import_tmdb_id" value="<?= $m['id'] ?>">
                        <input type="hidden" name="title" value="<?= htmlspecialchars($m['title']) ?>">
                        <input type="hidden" name="language" value="<?= htmlspecialchars($display_lang) ?>">
                        <input type="hidden" name="release_date" value="<?= $m['release_date'] ?>">
                        <input type="hidden" name="description" value="<?= htmlspecialchars($m['overview']) ?>">
                        <input type="hidden" name="rating" value="<?= htmlspecialchars($m['vote_average']) ?>">
                        <button type="submit" class="btn btn-outline-danger w-100 fw-bold"><i class="fas fa-plus-circle me-1"></i> Import to CineTime</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php elseif (isset($_GET['fetch']) && empty($error)): ?>
    <div class="alert alert-warning">No movies currently match the Indian region + language constraints from TMDB.</div>
<?php else: ?>
    <div class="card text-center bg-dark border-secondary p-5 mt-4">
        <i class="fas fa-film fa-4x text-secondary mb-3"></i>
        <h4 class="text-white">Ready to Import</h4>
        <p class="text-secondary">Click the fetch button to pull the latest Indian and English real-world movie entries directly from TMDB.</p>
        <p class="small text-danger"><i class="fas fa-lock"></i> API Configuration is securely bridged via <code>admin/config_tmdb.php</code>.</p>
    </div>
<?php endif; ?>

<style>
.card:hover { transform: scale(1.02); border-color: #e50914 !important; }
</style>

<?php require_once 'includes/footer.php'; ?>
