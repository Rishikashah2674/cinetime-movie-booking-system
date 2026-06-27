<?php
include 'connection.php'; // Include database connection

// Create (Insert a Booked Seat)
if (isset($_POST['create'])) {
    $booking_id = $_POST['booking_id'];
    $seat_id = $_POST['seat_id'];

    $stmt = $conn->prepare("INSERT INTO booked_seats (booking_id, seat_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $booking_id, $seat_id);

    if (!$stmt->execute()) {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Read (Retrieve booked seats)
$result = $conn->query("SELECT bs.booking_seat_id, bs.booking_id, bs.seat_id, 
                               u.name AS user_name, s.seat_number, b.show_time, 
                               b.total_amount, b.payment_status, b.booked_at
                        FROM booked_seats bs
                        JOIN bookings b ON bs.booking_id = b.booking_id
                        JOIN users u ON b.user_id = u.user_id
                        JOIN seats s ON bs.seat_id = s.seat_id");

// Update Booked Seat
if (isset($_POST['update'])) {
    $booking_seat_id = $_POST['booking_seat_id'];
    $booking_id = $_POST['booking_id'];
    $seat_id = $_POST['seat_id'];

    $stmt = $conn->prepare("UPDATE booked_seats SET booking_id=?, seat_id=? WHERE booking_seat_id=?");
    $stmt->bind_param("iii", $booking_id, $seat_id, $booking_seat_id);

    if (!$stmt->execute()) {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Delete Booked Seat
if (isset($_POST['delete'])) {
    $booking_seat_id = $_POST['booking_seat_id'];

    $stmt = $conn->prepare("DELETE FROM booked_seats WHERE booking_seat_id=?");
    $stmt->bind_param("i", $booking_seat_id);

    if (!$stmt->execute()) {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Booked Seats Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2, h3 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #f4f4f4; }
        form { margin-bottom: 10px; }
        input, select, button { padding: 8px; margin: 5px; }
        button { cursor: pointer; }
    </style>
</head>
<body>
    <h2>Manage Booked Seats</h2>

    <form method="post">
        <input type="number" name="booking_id" placeholder="Booking ID" required>
        <input type="number" name="seat_id" placeholder="Seat ID" required>
        <button type="submit" name="create">Add Booked Seat</button>
    </form>

    <h3>Booked Seats List</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Seat</th>
            <th>Show Time</th>
            <th>Total Amount</th>
            <th>Payment Status</th>
            <th>Booked At</th>
            <th>Actions</th>
        </tr>
        <?php 
        while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($row['booking_seat_id']) ?></td>
                <td><?= htmlspecialchars($row['user_name']) ?></td>
                <td><?= htmlspecialchars($row['seat_number']) ?></td>
                <td><?= htmlspecialchars($row['show_time']) ?></td>
                <td>$<?= htmlspecialchars($row['total_amount']) ?></td>
                <td><?= htmlspecialchars($row['payment_status']) ?></td>
                <td><?= htmlspecialchars($row['booked_at']) ?></td>
                <td>
                    <!-- Update Form -->
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="booking_seat_id" value="<?= htmlspecialchars($row['booking_seat_id']) ?>">
                        <input type="number" name="booking_id" placeholder="New Booking ID" required>
                        <input type="number" name="seat_id" placeholder="New Seat ID" required>
                        <button type="submit" name="update">Update</button>
                    </form>

                    <!-- Delete Form -->
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="booking_seat_id" value="<?= htmlspecialchars($row['booking_seat_id']) ?>">
                        <button type="submit" name="delete" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>

<?php $conn->close(); // Close connection ?>
</body>
</html>
