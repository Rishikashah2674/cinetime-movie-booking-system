<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        body {
            background-color: #f8d7da;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-wrapper {
            max-width: 400px;
            background: white;
            padding: 30px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-align: center;
        }
        h1 {
            font-size: 28px;
            font-weight: bold;
            color: #721c24;
            text-decoration: underline;
        }
        .form-group {
            margin: 15px 0;
            text-align: left;
        }
        .form-group label {
            font-weight: bold;
            color: #c82333;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            font-size: 16px;
            outline: none;
        }
        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }
        .login-btn {
            width: 100%;
            padding: 10px;
            background-color: #dc3545;
            color: white;
            font-size: 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        .login-btn:hover {
            background-color: #c82333;
        }
    </style>
    <script type="text/javascript">
        function validation() {
            var mail = document.getElementById('txtemail').value;
            var password = document.getElementById('txtpwd').value;
            var isValid = true;

            document.getElementById('error').innerHTML = "";
            document.getElementById('errorpass').innerHTML = "";

            if (mail == "") {
                document.getElementById('error').innerHTML = "*Please enter Email ID";
                isValid = false;
            }
            if (password == "") {
                document.getElementById('errorpass').innerHTML = "*Please enter Password";
                isValid = false;
            }
            return isValid;
        }
    </script>
</head>

<body>
    <div class="login-wrapper">
        <h1>Admin Login</h1>
        <form action="" method="post" onsubmit="return validation()">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="txtemail" id="txtemail" placeholder="Enter Admin Email">
                <div id="error" class="error-message"></div>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="txtpwd" id="txtpwd" placeholder="Enter Password">
                <div id="errorpass" class="error-message"></div>
            </div>
            <button class="login-btn" type="submit" name="btnsubmit">Sign In</button>
        </form>
    </div>
</body>
</html>

<?php
if (isset($_POST["btnsubmit"])) {
    $mail = $_POST["txtemail"];
    $pwd = $_POST["txtpwd"];

    $conn = mysqli_connect("localhost", "root", "", "moviesticketbooking");
    
    $admin = "SELECT * FROM admins WHERE email='$mail' AND password='$pwd'";
    $adminresult = $conn->query($admin);
    $admincnt = mysqli_num_rows($adminresult);

    if ($admincnt == 1) {
        $row = $adminresult->fetch_assoc();
        $_SESSION["adminid"] = $row["adminid"];
        $_SESSION["email"] = $mail;
        header("location: adminhome.php");
    } else {
        echo "<script>alert('Invalid Admin Credentials');</script>";
        echo "<script>location.href='adminlogin.php';</script>";
    }
}
?>
