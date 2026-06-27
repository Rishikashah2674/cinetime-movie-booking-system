<?php
require 'connection.php'; // Include database connection

// Create (Insert)
if (isset($_POST['create'])) {
    $name = htmlspecialchars($_POST['name']);
    $location = htmlspecialchars($_POST['location']);
    $total_seats = intval($_POST['total_seats']);
    $created_at = date("Y-m-d H:i:s");

    $sql = "INSERT INTO theaters (name, location, total_seats, created_at) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssis", $name, $location, $total_seats, $created_at);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Theater added successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
    }
}

// Update
if (isset($_POST['update'])) {
    $theater_id = intval($_POST['theater_id']);
    $name = htmlspecialchars($_POST['name']);
    $location = htmlspecialchars($_POST['location']);
    $total_seats = intval($_POST['total_seats']);

    $sql = "UPDATE theaters SET name=?, location=?, total_seats=? WHERE theater_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $name, $location, $total_seats, $theater_id);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Theater updated successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
    }
}

// Delete
if (isset($_POST['delete'])) {
    $theater_id = intval($_POST['theater_id']);

    $sql = "DELETE FROM theaters WHERE theater_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $theater_id);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Theater deleted successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
    }
}

// Read (Retrieve)
$result = $conn->query("SELECT * FROM theaters");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Theater Management</title>
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
    <h2>Theater Management</h2>

    <form method="post">
        <input type="text" name="name" placeholder="Theater Name" required>
        <input type="text" name="location" placeholder="Location" required>
        <input type="number" name="total_seats" placeholder="Total Seats" required>
        <button type="submit" name="create">Add Theater</button>
    </form>

    <h3>Theater List</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Location</th>
            <th>Total Seats</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($row['theater_id']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['location']) ?></td>
                <td><?= htmlspecialchars($row['total_seats']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="theater_id" value="<?= htmlspecialchars($row['theater_id']) ?>">
                        <input type="text" name="name" placeholder="New Name" required>
                        <input type="text" name="location" placeholder="New Location" required>
                        <input type="number" name="total_seats" placeholder="New Seats" required>
                        <button type="submit" name="update">Update</button>
                    </form>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="theater_id" value="<?= htmlspecialchars($row['theater_id']) ?>">
                        <button type="submit" name="delete">Delete</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>

<?php $conn->close(); ?>
</body>
</html>
