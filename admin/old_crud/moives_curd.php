<?php
require 'connection.php'; // Including connection.php to use the database connection

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action == "create") {
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
    
    if ($action == "update") {
        $movie_id = $_POST['movie_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $genre = $_POST['genre'];
        $duration = $_POST['duration'];
        $release_date = $_POST['release_date'];
        $language = $_POST['language'];
        $poster_url = $_POST['poster_url'];
        
        $sql = "UPDATE movies SET title=?, description=?, genre=?, duration=?, release_date=?, language=?, poster_url=? WHERE movie_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssi", $title, $description, $genre, $duration, $release_date, $language, $poster_url, $movie_id);
        $stmt->execute();
    }
    
    if ($action == "delete") {
        $movie_id = $_POST['movie_id'];
        $sql = "DELETE FROM movies WHERE movie_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $movie_id);
        $stmt->execute();
    }
}

$result = $conn->query("SELECT * FROM movies");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Movie Management</title>
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
    
    <h3>Movie List</h3>
    <table border="1">
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
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= htmlspecialchars($row['movie_id']) ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td><?= htmlspecialchars($row['genre']) ?></td>
            <td><?= htmlspecialchars($row['duration']) ?></td>
            <td><?= htmlspecialchars($row['release_date']) ?></td>
            <td><?= htmlspecialchars($row['language']) ?></td>
            <td><img src="<?= htmlspecialchars($row['poster_url']) ?>" width="50"></td>
            <td>
                <form method="POST" style="display:inline-block;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="movie_id" value="<?= htmlspecialchars($row['movie_id']) ?>">
                    <button type="submit">Delete</button>
                </form>
                <form method="POST" style="display:inline-block;">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="movie_id" value="<?= htmlspecialchars($row['movie_id']) ?>">
                    <input type="text" name="title" value="<?= htmlspecialchars($row['title']) ?>" required>
                    <input type="text" name="description" value="<?= htmlspecialchars($row['description']) ?>" required>
                    <input type="text" name="genre" value="<?= htmlspecialchars($row['genre']) ?>" required>
                    <input type="text" name="duration" value="<?= htmlspecialchars($row['duration']) ?>" required>
                    <input type="date" name="release_date" value="<?= htmlspecialchars($row['release_date']) ?>" required>
                    <input type="text" name="language" value="<?= htmlspecialchars($row['language']) ?>" required>
                    <input type="text" name="poster_url" value="<?= htmlspecialchars($row['poster_url']) ?>" required>
                    <button type="submit">Update</button>
                </form>
            </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>

<?php $conn->close(); ?>