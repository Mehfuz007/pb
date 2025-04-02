<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include "config.php"; // Database connection

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, account_number, balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $account_number, $balance);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: url('DASHBOARD.jpEg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
        }
        .dashboard-container {
            background: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            max-width: 400px;
            margin: 50px auto;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .btn {
            display: block;
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            background-color: #007bff;
            color: white;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .logout-btn {
            background-color: #dc3545;
        }
        .logout-btn:hover {
            background-color: #a71d2a;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h2>PEOPLE BANK OF INDIA</h2>
        <h3>Welcome, <?php echo htmlspecialchars($name); ?>!</h3>
        <p><strong>Account Number:</strong> <?php echo htmlspecialchars($account_number); ?></p>
        <p>Your current balance: <strong>₹<?php echo number_format($balance, 2); ?></strong></p>

        <div class="dashboard-buttons">
            <form action="transaction.php">
                <button type="submit" class="btn">View Transactions</button>
            </form>
            <form action="transfer.php">
                <button type="submit" class="btn">Transfer Money</button>
            </form>
            <form action="logout.php">
                <button type="submit" class="btn logout-btn">Logout</button>
            </form>
        </div>
    </div>
</body>
</html>