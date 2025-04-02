<?php
session_start();
include "config.php"; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST["name"]) && !empty($_POST["phone"]) && !empty($_POST["email"]) && !empty($_POST["password"])) {
        $name = trim($_POST["name"]);
        $phone = trim($_POST["phone"]);
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);

        // Hash password for security
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        function generateAccountNumber($conn) {
            do {
                $account_number = "ACC" . rand(1000000000, 9999999999);
                $query = "SELECT account_number FROM users WHERE account_number = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("s", $account_number);
                $stmt->execute();
                $result = $stmt->get_result();
            } while ($result->num_rows > 0);

            return $account_number;
        }

        // Check if email already exists
        $check_email_sql = "SELECT email FROM users WHERE email = ?";
        $stmt = $conn->prepare($check_email_sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<p style='color: red;'>Error: Email already exists! Please use a different email.</p>";
        } else {
            $account_number = generateAccountNumber($conn);
            $sql = "INSERT INTO users (name, phone, email, password, account_number) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $name, $phone, $email, $hashed_password, $account_number);

            if ($stmt->execute()) {
                echo "<p style='color: green;'>Registration successful! Your account number is: " . $account_number . "</p>";
            } else {
                echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
            }
        }

        $stmt->close();
    } else {
        echo "<p style='color: red;'>Please fill in all fields!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style.css"> 
    <style>
        body {
            background: url('REGISTER.jpeg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            text-align: center;
            width: 350px;
        }
        h2, h3 {
            margin-bottom: 15px;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background: #0056b3;
        }
        a {
            display: block;
            margin-top: 10px;
            text-decoration: none;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>PEOPLE BANK OF INDIA</h2>
        <h3>Register</h3>
        <form method="post" action="register.php">
            <input type="text" name="name" placeholder="Enter Full Name" required>
            <input type="text" name="phone" placeholder="Enter Phone Number" required>
            <input type="email" name="email" placeholder="Enter Email" required>
            <input type="password" name="password" placeholder="Enter Password" required>
            <button type="submit">Register</button>
        </form>
        <a href="login.php">Already have an account? Login</a>
    </div>
</body>
</html>
