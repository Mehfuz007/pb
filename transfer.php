<?php
session_start();
include "config.php"; // Database connection

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$message = "";

// Fetch sender's balance, account number, and password hash
$stmt = $conn->prepare("SELECT balance, account_number, password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($balance, $sender_account, $hashed_password);
$stmt->fetch();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $receiver_email = trim($_POST["receiver_email"]);
    $receiver_account = trim($_POST["receiver_account"]);
    $amount = floatval($_POST["amount"]);
    $password = trim($_POST["password"]);

    if ($amount <= 0) {
        $message = "<p class='error'>Invalid transfer amount.</p>";
    } elseif (empty($receiver_email) && empty($receiver_account)) {
        $message = "<p class='error'>Please enter either email or account number.</p>";
    } elseif (!password_verify($password, $hashed_password)) {
        $message = "<p class='error'>Incorrect password!</p>";
    } else {
        // Determine search criteria
        if (!empty($receiver_email)) {
            $stmt = $conn->prepare("SELECT id, balance, account_number FROM users WHERE email = ?");
            $stmt->bind_param("s", $receiver_email);
        } elseif (!empty($receiver_account)) {
            $stmt = $conn->prepare("SELECT id, balance, account_number FROM users WHERE account_number = ?");
            $stmt->bind_param("s", $receiver_account);
        }

        if (isset($stmt)) {
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($receiver_id, $receiver_balance, $receiver_account);
                $stmt->fetch();

                // Prevent self-transfers
                if ($receiver_id == $user_id) {
                    $message = "<p class='error'>You cannot transfer money to yourself!</p>";
                } 
                // Check if sender has enough balance
                elseif ($amount <= $balance) {
                    $conn->begin_transaction(); // Start transaction

                    // Deduct amount from sender
                    $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
                    $stmt->bind_param("di", $amount, $user_id);
                    $stmt->execute();

                    // Add amount to receiver
                    $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                    $stmt->bind_param("di", $amount, $receiver_id);
                    $stmt->execute();

                    // Insert transaction record for sender (debit)
                    $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type, recipient_account, sender_account) VALUES (?, ?, 'debit', ?, ?)");
                    $stmt->bind_param("idss", $user_id, $amount, $receiver_account, $sender_account);
                    $stmt->execute();

                    // Insert transaction record for receiver (credit)
                    $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type, recipient_account, sender_account) VALUES (?, ?, 'credit', ?, ?)");
                    $stmt->bind_param("idss", $receiver_id, $amount, $sender_account, $receiver_account);
                    $stmt->execute();

                    $conn->commit(); // Commit transaction

                    $message = "<p class='success'>₹$amount transferred successfully!</p>";
                } else {
                    $message = "<p class='error'>Insufficient balance!</p>";
                }
            } else {
                $message = "<p class='error'>Receiver not found!</p>";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TRANSFER MONEY</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('TRANSFER.jpeg') no-repeat center center/cover;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            width: 400px;
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h2 {
            color: #333;
        }
        p {
            margin: 10px 0;
            font-size: 16px;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        input, button {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
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
        a {
            display: inline-block;
            margin-top: 10px;
            text-decoration: none;
            color: #007bff;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Transfer Money</h2>
        <p>Your Balance: <strong>₹<?php echo number_format($balance, 2); ?></strong></p>
        <p>Your Account Number: <strong><?php echo htmlspecialchars($sender_account); ?></strong></p>

        <?php echo $message; ?>

        <form method="POST">
            <input type="email" name="receiver_email" placeholder="Receiver's Email (Optional)">
            <input type="text" name="receiver_account" placeholder="Receiver's Account Number (Optional)">
            <input type="number" name="amount" placeholder="Amount" required min="1">
            <input type="password" name="password" placeholder="Enter Password" required>
            <button type="submit">Transfer</button>
        </form>

        <a href="dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>
