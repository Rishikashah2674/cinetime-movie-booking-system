<?php
session_start();
include_once "connection.php"; // Ensure this file correctly connects to your database

// Enable error reporting for debugging (Remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check if email and password are provided
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        // Prepare the SQL statement securely
        $stmt = $conn->prepare("SELECT user_id, password FROM user WHERE email = ?");
        if (!$stmt) {
            die("SQL error: " . $conn->error); // Debugging error
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        // Check if user exists
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $hashed_password);
            $stmt->fetch();

            // Verify password
            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $user_id;
                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "User not found.";
        }

        $stmt->close();
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Timepass Tickets</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color:black;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: black;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px red;
            text-align: center;
            width: 350px;
        }
        .title {
            color:red;
        }
        .input-group {
            margin: 15px 0;
            text-align: left;
        }
        .input-group label {
            display: block;
            font-weight: bold;
            color:white;
        }
        .input-group input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px white;
            border-radius: 5px;
            outline: none;
        }
        .btn {
            background-color:red;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            margin-top: 10px;
        }
        .btn:hover {
            background-color:red;
        }
        .redirect {
            margin-top: 15px;
        }
        .redirect a {
            color:red;
            text-decoration: none;
            font-weight: bold;
        }
        .redirect a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="title">Login</h2>
        <?php if (isset($error)) { echo "<p style='color: red;'>$error</p>"; } ?>
        <form id="loginForm" method="POST">
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your mail">
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        <p class="redirect">Don't have an account? <a href="register.php">Register Here</a></p>
    </div>
</body>
</html>