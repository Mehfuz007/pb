<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "OBS1";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to generate a random account number
function generateAccountNumber($conn) {
    do {
        $account_number = "ACC" . rand(1000000000, 9999999999); // Example: ACC1234567890
        $query = "SELECT account_number FROM Users WHERE account_number = '$account_number'";
        $result = $conn->query($query);
    } while ($result->num_rows > 0); // Ensure uniqueness

    return $account_number;
}

// Assuming user inputs are received via a registration form
$first_name = "John";
$last_name = "Doe";
$email = "john@example.com";
$phone = "9876543210";
$password_hash = password_hash("password123", PASSWORD_DEFAULT); // Encrypt password
$account_number = generateAccountNumber($conn); // Generate unique account number

// Insert into Users table
$sql = "INSERT INTO Users (first_name, last_name, email, phone, password_hash, account_number) 
        VALUES ('$first_name', '$last_name', '$email', '$phone', '$password_hash', '$account_number')";

if ($conn->query($sql) === TRUE) {
    echo "Registration successful! Your account number is: " . $account_number;
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
