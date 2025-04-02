<?php
$host = "localhost"; 
$user = "root"; 
$pass = ""; 
$db = "OBS1";

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>
