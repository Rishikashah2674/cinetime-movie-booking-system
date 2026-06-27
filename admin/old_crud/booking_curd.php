<?php
include 'connection.php'; // Include the database connection

// Create (Insert Booking)
if (isset($_POST['create'])) {
    $user_id = $_POST['user_id'];
    $show_time = $_POST['show_time'];
    $total_amount = $_POST['total_amount'];
    $payment_status = $_POST['payment_status'];

    $stmt = $conn->prepare("INSERT INTO bookings (user_id, show_time, total_amount, payment_status, booked_at) 
                            VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("isss", $user_id, $show_time, $total_amount, $payment_status);

    if (!$stmt->execute()) {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Read (Retrieve Bookings)
$result = $conn->query("SELECT * FROM bookings");

// Update Booking
if (isset($_POST['update'])) {
    $booking_id = $_POST['booking_id'];
    $user_id = $_POST['user_id'];
    $show_time = $_POST['show_time'];
    $total_amount = $_POST['total_amount'];
    $payment_status = $_POST['payment_status'];

    $stmt = $conn->prepare("UPDATE bookings SET 
                            user_id=?, show_time=?, total_amount=?, payment_status=? 
                            WHERE booking_id=?");
    $stmt->bind_param("isssi", $user_id, $show_time, $total_amount, $payment_status, $booking_id);

    if (!$stmt->execute()) {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Delete Booking
if (isset($_POST['delete'])) {
    $booking_id = $_POST['booking_id'];

    $stmt = $conn->prepare("DELETE FROM bookings WHERE booking_id=?");
    $stmt->bind_param("i", $booking_id);

    if (!$stmt->execute()) {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Bookings Management</title>
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
    <h2>Manage Bookings</h2>

    <form method="post">
        <input type="number" name="user_id" placeholder="User ID" required>
        <input type="datetime-local" name="show_time" required>
        <input type="number" step="0.01" name="total_amount" placeholder="Total Amount" required>
        <select name="payment_status" required>
            <option value="Paid">Paid</option>
            <option value="Pending">Pending</option>
        </select>
        <button type="submit" name="create">Add Booking</button>
    </form>

    <h3>Bookings List</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>User ID</th>
            <th>Show Time</th>
            <th>Total Amount</th>
            <th>Payment Status</th>
            <th>Booked At</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($row['booking_id']) ?></td>
                <td><?= htmlspecialchars($row['user_id']) ?></td>
                <td><?= htmlspecialchars($row['show_time']) ?></td>
                <td>$<?= htmlspecialchars($row['total_amount']) ?></td>
                <td><?= htmlspecialchars($row['payment_status']) ?></td>
                <td><?= htmlspecialchars($row['booked_at']) ?></td>
                <td>
                    <!-- Update Form -->
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="booking_id" value="<?= htmlspecialchars($row['booking_id']) ?>">
                        <input type="number" name="user_id" placeholder="New User ID" required>
                        <input type="datetime-local" name="show_time" required>
                        <input type="number" step="0.01" name="total_amount" placeholder="New Total Amount" required>
                        <select name="payment_status" required>
                            <option value="Paid">Paid</option>
                            <option value="Pending">Pending</option>
                        </select>
                        <button type="submit" name="update">Update</button>
                    </form>

                    <!-- Delete Form -->
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="booking_id" value="<?= htmlspecialchars($row['booking_id']) ?>">
                        <button type="submit" name="delete" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>

<?php $conn->close(); // Close connection ?>
</body>
</html>
