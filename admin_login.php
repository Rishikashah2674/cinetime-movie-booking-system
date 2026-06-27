<?php
session_start();
require_once 'connection.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT admin_id, password FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($admin_id, $hashed_password);
            $stmt->fetch();
            
            if (password_verify($password, $hashed_password)) {
                // Login successful
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin_id;
                $_SESSION['admin_username'] = $username;
                header('Location: admin/dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            $error = 'Invalid username or password.';
        }
        $stmt->close();
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - CineTime</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        body {
            background-color: #0f0f13;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            font-family: 'Open Sans', sans-serif;
            margin: 0;
        }
        .login-card {
            background: #1c1c24;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.8);
            width: 100%;
            max-width: 400px;
            border: 1px solid #2d2d3a;
        }
        .login-card h2 {
            font-weight: 700;
            color: #e50914;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-control {
            background-color: #2a2a36;
            border: 1px solid #3e3e4f;
            color: #fff;
            padding: 12px;
        }
        .form-control:focus {
            background-color: #353545;
            border-color: #e50914;
            color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(229, 9, 20, 0.25);
        }
        .btn-danger {
            background-color: #e50914;
            border: none;
            padding: 12px;
            font-weight: bold;
            font-size: 1.1rem;
            width: 100%;
            border-radius: 6px;
            transition: all 0.3s;
        }
        .btn-danger:hover {
            background-color: #f40612;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(229, 9, 20, 0.4);
        }
        .form-label {
            color: #b3b3b3;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>🛠️ CineTime Admin</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required placeholder="Enter admin username">
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required placeholder="Enter admin password">
            </div>
            <button type="submit" class="btn btn-danger">Secure Login</button>
        </form>
    </div>
</body>
</html>
