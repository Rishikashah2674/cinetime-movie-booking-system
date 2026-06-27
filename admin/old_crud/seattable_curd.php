<?php
require 'connection.php'; // Include database connection

// Create (Insert)
if (isset($_POST['create'])) {
    $booking_id = intval($_POST['booking_id']);
    $seat_id = intval($_POST['seat_id']);

    $sql = "INSERT INTO seat_table (booking_id, seat_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $booking_id, $seat_id);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Booking seat added successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
    }
}

// Update
if (isset($_POST['update'])) {
    $booking_seat_id = intval($_POST['booking_seat_id']);
    $booking_id = intval($_POST['booking_id']);
    $seat_id = intval($_POST['seat_id']);

    $sql = "UPDATE seat_table SET booking_id=?, seat_id=? WHERE booking_seat_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $booking_id, $seat_id, $booking_seat_id);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Booking seat updated successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
    }
}

// Delete
if (isset($_POST['delete'])) {
    $booking_seat_id = intval($_POST['booking_seat_id']);
    
    $sql = "DELETE FROM seat_table WHERE booking_seat_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $booking_seat_id);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Booking seat deleted successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
    }
}

// Read (Retrieve)
$result = $conn->query("SELECT * FROM seat_table");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Seat Table Management</title>
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
    <h2>Seat Table Management</h2>

    <form method="post">
        <input type="text" name="booking_id" placeholder="Booking ID" required>
        <input type="text" name="seat_id" placeholder="Seat ID" required>
        <button type="submit" name="create">Add Booking Seat</button>
    </form>

    <h3>Seat Bookings</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Booking ID</th>
            <th>Seat ID</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($row['booking_seat_id']) ?></td>
                <td><?= htmlspecialchars($row['booking_id']) ?></td>
                <td><?= htmlspecialchars($row['seat_id']) ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="booking_seat_id" value="<?= htmlspecialchars($row['booking_seat_id']) ?>">
                        <input type="text" name="booking_id" placeholder="New Booking ID" required>
                        <input type="text" name="seat_id" placeholder="New Seat ID" required>
                        <button type="submit" name="update">Update</button>
                    </form>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="booking_seat_id" value="<?= htmlspecialchars($row['booking_seat_id']) ?>">
                        <button type="submit" name="delete">Delete</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>

<?php $conn->close(); ?>
</body>
</html>
