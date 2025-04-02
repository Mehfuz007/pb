<?php
session_start();
include "config.php"; // Connect to database

$success = ""; // Success message
$error = ""; // Error message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $old_password = trim($_POST["old_password"]);
    $new_password = trim($_POST["new_password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    // Check if new passwords match
    if ($new_password !== $confirm_password) {
        $error = "❌ New passwords do not match!";
    } elseif (strlen($new_password) < 6) {
        $error = "❌ Password must be at least 6 characters long!";
    } else {
        // Check if email exists and get the old password hash
        $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stored_password = $row["password"];

            // Verify old password
            if (password_verify($old_password, $stored_password)) {
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update password in the database
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->bind_param("ss", $hashed_password, $email);
                
                if ($stmt->execute()) {
                    $success = "✅ Password updated successfully! Redirecting to login...";
                    header("refresh:3; url=login.php"); // Auto redirect after 3 seconds
                } else {
                    $error = "❌ Failed to update password. Try again!";
                }
            } else {
                $error = "❌ Old password is incorrect!";
            }
        } else {
            $error = "❌ Email not found!";
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
    <title>Update Password | People Bank</title>
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
        <h3>Update Your Password</h3>

        <?php if ($error) echo "<p class='error'>$error</p>"; ?>
        <?php if ($success) echo "<p class='success'>$success</p>"; ?>

        <form method="post">
            <input type="email" name="email" placeholder="Enter your email" required>
            <input type="password" name="old_password" placeholder="Enter old password" required>
            <input type="password" name="new_password" placeholder="Enter new password" required>
            <input type="password" name="confirm_password" placeholder="Confirm new password" required>
            <button type="submit">Update Password</button>
        </form>

        <form action="login.php">
            <button type="submit" class="back-btn">Back to Login</button>
        </form>
    </div>
</body>
</html>
