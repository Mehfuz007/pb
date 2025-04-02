<?php
session_start();
include "config.php"; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = trim($_POST["phone"]);

    // Check if phone exists in users table
    $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['id'];

        // Generate a 6-digit OTP
        $otp = mt_rand(100000, 999999);
        $expiry_time = date("Y-m-d H:i:s", strtotime("+5 minutes"));

        // Store OTP in database
        $stmt = $conn->prepare("INSERT INTO user_otp (user_id, otp_code, expiry_time) VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE otp_code = VALUES(otp_code), expiry_time = VALUES(expiry_time)");
        $stmt->bind_param("iss", $user_id, $otp, $expiry_time);
        $stmt->execute();

        $_SESSION['otp_user_id'] = $user_id; // Store user ID for verification

        echo "OTP Sent: $otp"; // In real-world, send via SMS API
    } else {
        echo "Phone number not registered!";
    }
}
?>
