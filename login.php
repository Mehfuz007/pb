<?php
session_start();
include "config.php"; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Check if user exists
    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $name, $hashed_password);
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $hashed_password)) {
            $_SESSION["user_id"] = $id;
            $_SESSION["name"] = $name;
            header("Location: dashboard.php"); // Redirect to dashboard
            exit();
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "No user found with this email!";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: url('LOGIN LOGO.jpeg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            text-align: center;
        }
        .container {
            width: 350px;
            background: rgba(255, 255, 255, 0.9);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .btn {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            background: #28a745;
            color: white;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }
        .btn.update-btn {
            background: rgb(7, 121, 235);
        }
        .btn.back-btn {
            background: #dc3545;
        }
        .forgot-password {
            display: block;
            margin-top: 10px;
            color: #007bff;
            text-decoration: none;
        }
        .forgot-password:hover {
            text-decoration: underline;
        }
        img.logo {
            width: 100px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Bank Logo Image -->
        <img src="pbi.jpeg" alt="Bank Logo" class="logo">
        <h2>PEOPLE BANK OF INDIA</h2>
        <h3>Login</h3>
        <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
        
        <form method="post">
            <input type="email" name="email" placeholder="Enter Email" required>
            <input type="password" name="password" placeholder="Enter Password" required>
            <button type="submit" class="btn">Login</button>
        </form>

        <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
        
        <form action="update_password.php">
            <button type="submit" class="btn update-btn">Update Password</button>
        </form>

        <a href="index.html">
            <button type="button" class="btn back-btn">Back</button>
        </a>
        
        <a href="register.php">Don't have an account? Register</a>  
    </div>
</body>
</html>
