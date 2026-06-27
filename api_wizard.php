<?php
require_once 'connection.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'cities') {
    $sql = "SELECT DISTINCT t.city FROM theaters t JOIN shows s ON s.theater_id = t.id WHERE s.show_date >= CURDATE() ORDER BY t.city ASC";
    $res = $conn->query($sql);
    $cities = [];
    while ($row = $res->fetch_assoc()) {
        $cities[] = $row['city'];
    }
    echo json_encode(['success' => true, 'data' => $cities]);

} elseif ($action === 'movies') {
    $city = trim($_GET['city'] ?? '');
    $sql = "SELECT DISTINCT m.movie_id, m.title, m.poster_url, m.rating, m.genre, m.language, m.duration 
            FROM movies m 
            JOIN shows s ON s.movie_id = m.movie_id 
            JOIN theaters t ON s.theater_id = t.id 
            WHERE t.city = ? AND s.show_date >= CURDATE()
            ORDER BY m.title ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $city);
    $stmt->execute();
    $res = $stmt->get_result();
    $movies = [];
    while ($row = $res->fetch_assoc()) {
        $movies[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $movies]);

} elseif ($action === 'dates') {
    $movie_id = intval($_GET['movie_id'] ?? 0);
    $city = trim($_GET['city'] ?? '');

    $sql = "SELECT DISTINCT s.show_date 
            FROM shows s 
            JOIN theaters t ON s.theater_id = t.id 
            WHERE s.movie_id = ? AND t.city = ? AND s.show_date >= CURDATE() 
            ORDER BY s.show_date ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $movie_id, $city);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $dates = [];
    while ($row = $res->fetch_assoc()) {
        $dates[] = [
            'raw_date' => $row['show_date'],
            'display_date' => date('D, M d', strtotime($row['show_date']))
        ];
    }
    echo json_encode(['success' => true, 'data' => $dates]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid active endpoint constraint']);
}
$conn->close();
?>
