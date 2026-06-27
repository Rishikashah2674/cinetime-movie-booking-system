<?php
require 'connection.php'; // Include database connection

// Create (Insert)
if (isset($_POST['create'])) {
    $movie_id = intval($_POST['movie_id']);
    $theater_id = intval($_POST['theater_id']);
    $screen_id = intval($_POST['screen_id']);
    $show_date = $_POST['show_date'];
    $show_time = $_POST['show_time'];
    $price = floatval($_POST['price']);

    $sql = "INSERT INTO showtimes (movie_id, theater_id, screen_id, show_date, show_time, price) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiissd", $movie_id, $theater_id, $screen_id, $show_date, $show_time, $price);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Showtime added successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
    }
}

// Update
if (isset($_POST['update'])) {
    $showtime_id = intval($_POST['showtime_id']);
    $show_date = $_POST['show_date'];
    $show_time = $_POST['show_time'];
    $price = floatval($_POST['price']);

    $sql = "UPDATE showtimes SET show_date=?, show_time=?, price=? WHERE showtime_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdi", $show_date, $show_time, $price, $showtime_id);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Showtime updated successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
    }
}

// Delete
if (isset($_POST['delete'])) {
    $showtime_id = intval($_POST['showtime_id']);

    $sql = "DELETE FROM showtimes WHERE showtime_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $showtime_id);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Showtime deleted successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
    }
}

// Read (Retrieve)
$result = $conn->query("SELECT * FROM showtimes");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Showtimes Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1a1a1a;
            color: #fff;
            text-align: center;
        }
        h2 {
            color: #ffcc00;
        }
        table {
            width: 80%;
            margin: auto;
            border-collapse: collapse;
            background: #222;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ffcc00;
        }
        th {
            background: #444;
        }
        button {
            background: #ffcc00;
            color: #000;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: #e6b800;
        }
    </style>
</head>
<body>
    <h2>Showtimes Management</h2>

    <form method="post">
        <input type="text" name="movie_id" placeholder="Movie ID" required>
        <input type="text" name="theater_id" placeholder="Theater ID" required>
        <input type="text" name="screen_id" placeholder="Screen ID" required>
        <input type="date" name="show_date" required>
        <input type="time" name="show_time" required>
        <input type="text" name="price" placeholder="Price" required>
        <button type="submit" name="create">Add Showtime</button>
    </form>

    <h3>Showtimes List</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Movie ID</th>
            <th>Theater ID</th>
            <th>Screen ID</th>
            <th>Show Date</th>
            <th>Show Time</th>
            <th>Price</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($row['showtime_id']) ?></td>
                <td><?= htmlspecialchars($row['movie_id']) ?></td>
                <td><?= htmlspecialchars($row['theater_id']) ?></td>
                <td><?= htmlspecialchars($row['screen_id']) ?></td>
                <td><?= htmlspecialchars($row['show_date']) ?></td>
                <td><?= htmlspecialchars($row['show_time']) ?></td>
                <td><?= htmlspecialchars($row['price']) ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="showtime_id" value="<?= htmlspecialchars($row['showtime_id']) ?>">
                        <input type="date" name="show_date" required>
                        <input type="time" name="show_time" required>
                        <input type="text" name="price" placeholder="New Price" required>
                        <button type="submit" name="update">Update</button>
                    </form>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="showtime_id" value="<?= htmlspecialchars($row['showtime_id']) ?>">
                        <button type="submit" name="delete">Delete</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>

<?php $conn->close(); ?>
</body>
</html>
