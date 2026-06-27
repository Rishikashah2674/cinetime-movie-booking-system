<?php
require 'connection.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action == "create") {
        if (isset($_POST['screen_number'])) {
            $theater_id = $_POST['theater_id'];
            $screen_number = $_POST['screen_number'];
            $total_seats = $_POST['total_seats'];

            $sql = "INSERT INTO screens (theater_id, screen_number, total_seats) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $theater_id, $screen_number, $total_seats);
            $stmt->execute();
        } else {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $genre = $_POST['genre'];
            $duration = $_POST['duration'];
            $release_date = $_POST['release_date'];
            $language = $_POST['language'];
            $poster_url = $_POST['poster_url'];

            $sql = "INSERT INTO movies (title, description, genre, duration, release_date, language, poster_url, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssss", $title, $description, $genre, $duration, $release_date, $language, $poster_url);
            $stmt->execute();
        }
    }

    if ($action == "delete") {
        if (isset($_POST['screen_id'])) {
            $screen_id = $_POST['screen_id'];
            $sql = "DELETE FROM screens WHERE screen_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $screen_id);
            $stmt->execute();
        } else {
            $movie_id = $_POST['movie_id'];
            $sql = "DELETE FROM movies WHERE movie_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $movie_id);
            $stmt->execute();
        }
    }
}

$movies = $conn->query("SELECT * FROM movies");
$screens = $conn->query("SELECT * FROM screens");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Movie and Screen Management</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #1a1a1a; color: #fff; text-align: center; }
        h2, h3 { color: #ffcc00; }
        form { background: #333; padding: 15px; border-radius: 10px; display: inline-block; margin-bottom: 20px; }
        input, button { padding: 10px; margin: 5px; border: none; border-radius: 5px; }
        button { background: #ffcc00; color: #000; cursor: pointer; }
        button:hover { background: #e6b800; }
        table { width: 80%; margin: auto; border-collapse: collapse; background: #222; }
        th, td { padding: 10px; border: 1px solid #ffcc00; }
        th { background: #444; }
        img { width: 50px; border-radius: 5px; }
    </style>
</head>
<body>
    <h2>Movie Management</h2>
    <form method="POST">
        <input type="hidden" name="action" value="create">
        <input type="text" name="title" placeholder="Title" required>
        <input type="text" name="description" placeholder="Description" required>
        <input type="text" name="genre" placeholder="Genre" required>
        <input type="text" name="duration" placeholder="Duration" required>
        <input type="date" name="release_date" required>
        <input type="text" name="language" placeholder="Language" required>
        <input type="text" name="poster_url" placeholder="Poster URL" required>
        <button type="submit">Add Movie</button>
    </form>

    <h2>Screen Management</h2>
    <form method="POST">
        <input type="hidden" name="action" value="create">
        <input type="text" name="theater_id" placeholder="Theater ID" required>
        <input type="text" name="screen_number" placeholder="Screen Number" required>
        <input type="text" name="total_seats" placeholder="Total Seats" required>
        <button type="submit">Add Screen</button>
    </form>

    <h3>Screen List</h3>
    <table>
        <tr>
            <th>Screen ID</th>
            <th>Theater ID</th>
            <th>Screen Number</th>
            <th>Total Seats</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $screens->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['screen_id'] ?></td>
            <td><?= $row['theater_id'] ?></td>
            <td><?= $row['screen_number'] ?></td>
            <td><?= $row['total_seats'] ?></td>
            <td>
                <form method="POST" style="display:inline-block;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="screen_id" value="<?= $row['screen_id'] ?>">
                    <button type="submit">Delete</button>
                </form>
            </td>
        </tr>
        <?php } ?>
    </table>

    <h3>Movie List</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Description</th>
            <th>Genre</th>
            <th>Duration</th>
            <th>Release Date</th>
            <th>Language</th>
            <th>Poster</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $movies->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['movie_id'] ?></td>
            <td><?= $row['title'] ?></td>
            <td><?= $row['description'] ?></td>
            <td><?= $row['genre'] ?></td>
            <td><?= $row['duration'] ?></td>
            <td><?= $row['release_date'] ?></td>
            <td><?= $row['language'] ?></td>
            <td><img src="<?= $row['poster_url'] ?>" width="50"></td>
            <td>
                <form method="POST" style="display:inline-block;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="movie_id" value="<?= $row['movie_id'] ?>">
                    <button type="submit">Delete</button>
                </form>
            </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>

<?php $conn->close(); ?>
