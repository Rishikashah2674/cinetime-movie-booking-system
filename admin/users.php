<?php
require_once 'includes/auth.php';

// Handle user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM user WHERE user_id = ?");
    $stmt->bind_param("i", $del_id);
    if ($stmt->execute()) {
        $msg = "User deleted successfully!";
    } else {
        $error = "Failed to delete user.";
    }
    $stmt->close();
}

require_once 'includes/header.php';

// Fetch users
$users = $conn->query("SELECT * FROM user ORDER BY created_at DESC");
?>

<div class="card">
    <div class="card-header">
        Manage Registered Users
    </div>
    <div class="card-body">
        <?php if(isset($msg)) echo "<div class='alert alert-success'>$msg</div>"; ?>
        <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Registered Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($users && $users->num_rows > 0): ?>
                        <?php while($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?= $user['user_id'] ?></td>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['phone']) ?></td>
                            <td><?= date('d M Y, h:i A', strtotime($user['created_at'])) ?></td>
                            <td>
                                <a href="?delete=<?= $user['user_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this user?');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-4">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
