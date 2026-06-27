<?php
include 'connection.php'; // Database connection

// Create (Insert Payment)
if (isset($_POST['create'])) {
    $booking_id = $_POST['booking_id'];
    $user_id = $_POST['user_id'];
    $amount = $_POST['amount'];
    $payment_status = $_POST['payment_status'];
    $transaction_id = $_POST['transaction_id'];

    $stmt = $conn->prepare("INSERT INTO payments (booking_id, user_id, amount, payment_status, transaction_id, paid_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iidss", $booking_id, $user_id, $amount, $payment_status, $transaction_id);
    $stmt->execute();
    $stmt->close();
}

// Read (Retrieve Payments)
$result = $conn->query("SELECT * FROM payments");

// Update Payment
if (isset($_POST['update'])) {
    $payment_id = $_POST['payment_id'];
    $booking_id = $_POST['booking_id'];
    $user_id = $_POST['user_id'];
    $amount = $_POST['amount'];
    $payment_status = $_POST['payment_status'];
    $transaction_id = $_POST['transaction_id'];

    $stmt = $conn->prepare("UPDATE payments SET booking_id=?, user_id=?, amount=?, payment_status=?, transaction_id=? WHERE payment_id=?");
    $stmt->bind_param("iidssi", $booking_id, $user_id, $amount, $payment_status, $transaction_id, $payment_id);
    $stmt->execute();
    $stmt->close();
}

// Delete Payment
if (isset($_POST['delete'])) {
    $payment_id = $_POST['payment_id'];
    $stmt = $conn->prepare("DELETE FROM payments WHERE payment_id=?");
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Payments Management</title>
</head>
<body>
    <h2>Manage Payments</h2>
    <form method="post">
        <input type="text" name="booking_id" placeholder="Booking ID" required>
        <input type="text" name="user_id" placeholder="User ID" required>
        <input type="text" name="amount" placeholder="Amount" required>
        <input type="text" name="transaction_id" placeholder="Transaction ID" required>
        <select name="payment_status" required>
            <option value="Paid">Paid</option>
            <option value="Pending">Pending</option>
        </select>
        <button type="submit" name="create">Add Payment</button>
    </form>

    <h3>Payments List</h3>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Booking ID</th>
            <th>User ID</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Transaction ID</th>
            <th>Paid At</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($row['payment_id']) ?></td>
                <td><?= htmlspecialchars($row['booking_id']) ?></td>
                <td><?= htmlspecialchars($row['user_id']) ?></td>
                <td><?= htmlspecialchars($row['amount']) ?></td>
                <td><?= htmlspecialchars($row['payment_status']) ?></td>
                <td><?= htmlspecialchars($row['transaction_id']) ?></td>
                <td><?= htmlspecialchars($row['paid_at']) ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="payment_id" value="<?= htmlspecialchars($row['payment_id']) ?>">
                        <input type="text" name="booking_id" placeholder="New Booking ID" required>
                        <input type="text" name="user_id" placeholder="New User ID" required>
                        <input type="text" name="amount" placeholder="New Amount" required>
                        <input type="text" name="transaction_id" placeholder="New Transaction ID" required>
                        <select name="payment_status" required>
                            <option value="Paid">Paid</option>
                            <option value="Pending">Pending</option>
                        </select>
                        <button type="submit" name="update">Update</button>
                    </form>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="payment_id" value="<?= htmlspecialchars($row['payment_id']) ?>">
                        <button type="submit" name="delete">Delete</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>

<?php $conn->close(); ?>
</body>
</html>
