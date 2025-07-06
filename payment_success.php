<?php
session_start();

// if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'player') {
//     header("Location: login4.html");
//     exit();
// }

if (!isset($_GET['pay_id'])) {
    header("Location: payment_form.php?error=No payment ID provided");
    exit();
}

$pay_id = $_GET['pay_id'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success | Legacy Sports</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f0e6;
            color: #3e2723;
            line-height: 1.6;
            padding: 20px;
        }

        .success-container {
            max-width: 600px;
            margin: 50px auto;
            background: #efebe9;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid #d7ccc8;
            text-align: center;
        }

        h1 {
            color: #5d4037;
            margin-bottom: 20px;
        }

        .success-icon {
            color: #2e7d32;
            font-size: 64px;
            margin-bottom: 20px;
        }

        .success-message {
            font-size: 18px;
            margin-bottom: 30px;
        }

        .payment-details {
            background-color: #e8f5e9;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 30px;
            text-align: left;
            border: 1px solid #c8e6c9;
        }

        .payment-id {
            font-weight: bold;
            color: #2e7d32;
        }

        .button {
            background-color: #8d6e63;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }

        .button:hover {
            background-color: #6d4c41;
        }
    </style>
</head>

<body>
    <div class="success-container">
        <div class="success-icon">✓</div>
        <h1>Payment Successful!</h1>
        <div class="success-message">
            Thank you for your payment. Your transaction has been completed successfully.
        </div>

        <div class="payment-details">
            <p><strong>Payment Reference:</strong> <span class="payment-id"><?php echo htmlspecialchars($pay_id); ?></span></p>
            <p><strong>Date:</strong> <?php echo date('F j, Y'); ?></p>
        </div>

        <a href="submit_schedule.php" class="button">Return to Dashboard</a>
    </div>
</body>

</html>