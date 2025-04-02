<?php
session_start();
include "config.php"; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['otp_user_id'] ?? null;
    $entered_otp = trim($_POST["otp"]);

    if ($user_id) {
        // Fetch OTP from the database
        $stmt = $conn->prepare("SELECT otp_code, expiry_time FROM user_otp WHERE user_id = ? AND expiry_time > NOW()");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($entered_otp === $row['otp_code']) {
                $_SESSION['verified'] = true; // OTP Verified
                echo "OTP Verified! Redirecting to password reset.";
                header("Location: update_password.php");
                exit();
            } else {
                echo "Invalid OTP!";
            }
        } else {
            echo "OTP Expired or Incorrect!";
        }
    } else {
        echo "Session Expired! Request OTP Again.";
    }
}
?>
