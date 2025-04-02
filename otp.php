<?php
session_start();

// Function to generate OTP
function generateOtp() {
    return rand(100000, 999999); // Generates a random number between 100000 and 999999
}

// Process OTP generation and email sending
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]); // User's email

    // Validate the email (basic validation)
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email address!";
        exit();
    }

    // Generate OTP
    $otp = generateOtp();
    $_SESSION["otp"] = $otp; // Store OTP in session for later verification

    // Send OTP via email using PHP's mail() function
    $subject = "Your OTP Code";
    $message = "Your OTP code is: $otp";
    $headers = "From: no-reply@yourdomain.com"; // Replace with your email address

    if (mail($email, $subject, $message, $headers)) {
        echo "OTP sent to your email address!";
    } else {
        echo "Failed to send OTP.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate OTP</title>
</head>
<body>
    <h2>Enter Your Email to Receive OTP</h2>
    <form method="POST" action="">
        <input type="email" name="email" placeholder="Enter your email" required>
        <button type="submit">Generate OTP</button>
    </form>
</body>
</html>
