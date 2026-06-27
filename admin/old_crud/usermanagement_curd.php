<?php
require 'connection.php'; // Include database connection

$message = ""; // To store success/error messages

// Create (Insert User)
if (isset($_POST['create'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = htmlspecialchars($_POST['phone']);

    $stmt = $conn->prepare("INSERT INTO user (name, email, password, phone, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $name, $email, $password, $phone);

    if ($stmt->execute()) {
        $message = "<p class='alert alert-success'>User added successfully!</p>";
    } else {
        $message = "<p class='alert alert-danger'>Error: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Update User
if (isset($_POST['update'])) {
    $user_id = intval($_POST['user_id']);
    $name = htmlspecialchars($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars($_POST['phone']);

    $stmt = $conn->prepare("UPDATE user SET name=?, email=?, phone=? WHERE user_id=?");
    $stmt->bind_param("sssi", $name, $email, $phone, $user_id);

    if ($stmt->execute()) {
        $message = "<p class='alert alert-success'>User updated successfully!</p>";
    } else {
        $message = "<p class='alert alert-danger'>Error: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Delete User
if (isset($_POST['delete'])) {
    $user_id = intval($_POST['user_id']);

    $stmt = $conn->prepare("DELETE FROM user WHERE user_id=?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        $message = "<p class='alert alert-success'>User deleted successfully!</p>";
    } else {
        $message = "<p class='alert alert-danger'>Error: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Read (Fetch Users)
$result = $conn->query("SELECT * FROM user");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2 class="mb-4">User Management</h2>

    <!-- Success/Error Messages -->
    <?= $message ?>

    <!-- Create User Form -->
    <form method="post" class="mb-4">
        <div class="row g-2">
            <div class="col-md-3">
                <input type="text" name="name" class="form-control" placeholder="Name" required>
            </div>
            <div class="col-md-3">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="col-md-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <div class="col-md-2">
                <input type="text" name="phone" class="form-control" placeholder="Phone" required>
            </div>
            <div class="col-md-1">
                <button type="submit" name="create" class="btn btn-primary">Add</button>
            </div>
        </div>
    </form>

    <!-- User List -->
    <table class="table table-bordered">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($row['user_id']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['phone']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td>
                    <!-- Update Form -->
                    <form method="post" class="d-inline">
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($row['user_id']) ?>">
                        <input type="text" name="name" value="<?= htmlspecialchars($row['name']) ?>" required>
                        <input type="email" name="email" value="<?= htmlspecialchars($row['email']) ?>" required>
                        <input type="text" name="phone" value="<?= htmlspecialchars($row['phone']) ?>" required>
                        <button type="submit" name="update" class="btn btn-warning btn-sm">Update</button>
                    </form>

                    <!-- Delete Form -->
                    <form method="post" class="d-inline">
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($row['user_id']) ?>">
                        <button type="submit" name="delete" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>

<?php $conn->close(); ?>
</body>
</html>
