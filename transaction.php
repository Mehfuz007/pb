<?php
session_start();
include "config.php"; // Database connection

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch transactions for the logged-in user
$stmt = $conn->prepare("SELECT amount, type, recipient_account, sender_account, created_at FROM transactions WHERE user_id = ? ORDER BY created_at DESC");

if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($amount, $type, $recipient_account, $sender_account, $created_at);
} else {
    die("Database error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TRANSACTION HISTORY</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('TRANSACTION-HISTORY.jpeg') no-repeat center center/cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            width: 80%;
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h2 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background: #28a745;
            color: white;
        }
        .credit {
            color: green;
            font-weight: bold;
        }
        .debit {
            color: red;
            font-weight: bold;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Transaction History</h2>

        <?php if ($stmt->num_rows > 0) { ?>
            <table>
                <tr>
                    <th>Amount</th>
                    <th>Type</th>
                    <th>From/To Account</th>
                    <th>Date</th>
                </tr>
                <?php while ($stmt->fetch()) { ?>
                    <tr>
                        <td>₹<?php echo number_format($amount, 2); ?></td>
                        <td class="<?php echo ($type == 'credit') ? 'credit' : 'debit'; ?>">
                            <?php echo ucfirst($type); ?>
                        </td>
                        <td>
                            <?php
                            if ($type == 'debit' && $recipient_account) {
                                echo "To: " . htmlspecialchars($recipient_account);
                            } elseif ($type == 'credit' && $sender_account) {
                                echo "From: " . htmlspecialchars($sender_account);
                            } else {
                                echo "-";
                            }
                            ?>
                        </td>
                        <td><?php echo $created_at; ?></td>
                    </tr>
                <?php } ?>
            </table>
        <?php } else { ?>
            <p>No transactions found.</p>
        <?php } ?>

        <a href="dashboard.php" class="btn">Back to Dashboard</a>
    </div>
</body>
</html>

<?php
$stmt->close();
?>
