<?php
session_start();


if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'player') {
    header("Location: login4.html");
    exit();
}


$player_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment | Legacy Sports</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f0e6;
            color: #3e2723;
            line-height: 1.6;
            padding: 20px;
        }

        .payment-container {
            max-width: 600px;
            margin: 30px auto;
            background: #efebe9;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid #d7ccc8;
        }

        h1 {
            color: #5d4037;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #5d4037;
            font-weight: 500;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #8d6e63;
            border-radius: 6px;
            background-color: #f5f0e6;
            font-size: 16px;
        }

        input[readonly] {
            background-color: #e0d5ce;
            cursor: not-allowed;
        }

        button {
            background-color: #8d6e63;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            margin-top: 20px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #6d4c41;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }

        .error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }

        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }
    </style>
</head>

<body>
    <div class="payment-container">
        <h1>Make Payment</h1>

        <?php if (isset($_GET['error'])): ?>
            <div class="message error">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <form id="payment-form" action="create_checkout_session.php" method="POST">
            <div class="form-group">
                <label for="player_id">Player ID:</label>
                <input type="text" id="player_id" name="player_id" value="<?php echo htmlspecialchars($player_id); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="amount">Amount (USD):</label>
                <input type="number" id="amount" name="amount" min="1" step="0.01" required>
            </div>

            <button type="submit" id="checkout-button">Proceed to Payment</button>
        </form>
    </div>
</body>

</html>