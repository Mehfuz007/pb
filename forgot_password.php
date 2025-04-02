<?php
session_start();
include "config.php"; // Database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; // Ensure you have PHPMailer installed

$success = "";
$error = "";

// Generate OTP and send it to the user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["send_otp"])) {
    $email = trim($_POST["email"]);
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['email'] = $email;
        $_SESSION['otp'] = rand(100000, 999999); // Generate a 6-digit OTP

        // Send OTP via email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'your-email@gmail.com'; // Your email
            $mail->Password = 'your-email-password'; // Your email password or app password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('your-email@gmail.com', 'People Bank');
            $mail->addAddress($email);
            $mail->Subject = 'Your OTP for Password Reset';
            $mail->Body = "Your OTP for password reset is: " . $_SESSION['otp'];

            $mail->send();
            $success = "✅ OTP sent to your email!";
        } catch (Exception $e) {
            $error = "❌ Could not send OTP. Try again!";
        }
    } else {
        $error = "❌ Email not found!";
    }
    $stmt->close();
}

// Verify OTP and update password
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["verify_otp"])) {
    $entered_otp = trim($_POST["otp"]);
    $new_password = trim($_POST["new_password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    if (!isset($_SESSION['otp']) || $entered_otp != $_SESSION['otp']) {
        $error = "❌ Invalid OTP!";
    } elseif ($new_password !== $confirm_password) {
        $error = "❌ Passwords do not match!";
    } elseif (strlen($new_password) < 6) {
        $error = "❌ Password must be at least 6 characters!";
    } else {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $email = $_SESSION['email'];

        // Update the password in the database
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);

        if ($stmt->execute()) {
            $success = "✅ Password updated successfully! Redirecting to login...";
            session_unset();
            session_destroy();
            header("refresh:3; url=login.php"); // Redirect after 3 seconds
        } else {
            $error = "❌ Failed to update password!";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | People Bank</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('PASSWORD.jpeg') no-repeat center center/cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            width: 400px;
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            text-align: center;
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        h2 {
            color: #007bff;
            font-size: 22px;
        }
        h3 {
            color: #333;
            margin-bottom: 10px;
        }
        p {
            font-weight: bold;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        input, button {
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            background: #007bff;
            color: white;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background: #0056b3;
        }
        .back-btn {
            background: #555;
        }
        .back-btn:hover {
            background: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>PEOPLE BANK OF INDIA</h2>
        <h3>Forgot Password</h3>

        <?php if ($error) echo "<p class='error'>$error</p>"; ?>
        <?php if ($success) echo "<p class='success'>$success</p>"; ?>

        <!-- Step 1: Enter Email -->
        <?php if (!isset($_SESSION['otp'])) { ?>
            <form method="post">
                <input type="email" name="email" placeholder="Enter your email" required>
                <button type="submit" name="send_otp">Send OTP</button>
            </form>
        <?php } ?>

        <!-- Step 2: Enter OTP & New Password -->
        <?php if (isset($_SESSION['otp'])) { ?>
            <form method="post">
                <input type="text" name="otp" placeholder="Enter OTP" required>
                <input type="password" name="new_password" placeholder="Enter new password" required>
                <input type="password" name="confirm_password" placeholder="Confirm new password" required>
                <button type="submit" name="verify_otp">Verify OTP & Update Password</button>
            </form>
        <?php } ?>

        <form action="login.php">
            <button type="submit" class="back-btn">Back to Login</button>
        </form>
    </div>
</body>
</html>
