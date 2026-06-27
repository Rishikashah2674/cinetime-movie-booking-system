<?php
require 'connection.php'; // Include database connection

// Create (Insert)
if (isset($_POST['create'])) {
    $screen_id = intval($_POST['screen_id']);
    $seat_number = htmlspecialchars($_POST['seat_number']);
    $seat_type = htmlspecialchars($_POST['seat_type']);
    $price = floatval($_POST['price']);

    $sql = "INSERT INTO seats (screen_id, seat_number, seat_type, price) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issd", $screen_id, $seat_number, $seat_type, $price);
    $stmt->execute();
}

// Read (Retrieve)
$result = $conn->query("SELECT * FROM seats");

// Update
if (isset($_POST['update'])) {
    $seat_id = intval($_POST['seat_id']);
    $seat_type = htmlspecialchars($_POST['seat_type']);
    $price = floatval($_POST['price']);

    $sql = "UPDATE seats SET seat_type=?, price=? WHERE seat_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdi", $seat_type, $price, $seat_id);
    $stmt->execute();
}

// Delete
if (isset($_POST['delete'])) {
    $seat_id = intval($_POST['seat_id']);
    $sql = "DELETE FROM seats WHERE seat_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $seat_id);
    $stmt->execute();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Seats Management</title>
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
    <h2>Seats Management</h2>

    <form method="post">
        <input type="text" name="screen_id" placeholder="Screen ID" required>
        <input type="text" name="seat_number" placeholder="Seat Number" required>
        <input type="text" name="seat_type" placeholder="Seat Type" required>
        <input type="text" name="price" placeholder="Price" required>
        <button type="submit" name="create">Add Seat</button>
    </form>

    <h3>Seats List</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Screen ID</th>
            <th>Seat Number</th>
            <th>Seat Type</th>
            <th>Price</th>
            <th>Actions</th>
        </tr>
        <?php
        require 'connection.php';
        $result = $conn->query("SELECT * FROM seats");
        while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= $row['seat_id'] ?></td>
                <td><?= $row['screen_id'] ?></td>
                <td><?= $row['seat_number'] ?></td>
                <td><?= $row['seat_type'] ?></td>
                <td><?= $row['price'] ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="seat_id" value="<?= $row['seat_id'] ?>">
                        <input type="text" name="seat_type" placeholder="New Type" required>
                        <input type="text" name="price" placeholder="New Price" required>
                        <button type="submit" name="update">Update</button>
                    </form>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="seat_id" value="<?= $row['seat_id'] ?>">
                        <button type="submit" name="delete">Delete</button>
                    </form>
                </td>
            </tr>
        <?php } $conn->close(); ?>
    </table>
</body>
</html>
