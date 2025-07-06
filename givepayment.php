<?php
session_start();

// Database configuration
$host = "localhost";
$user = "root";
$password = "";
$dbname = "legacy_db";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_payment'])) {
    // Check if user is logged in
    if (!isset($_SESSION['player_id'])) {
        header("Location: login4.php");
        exit();
    }

    // Create connection
    $conn = new mysqli($host, $user, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get form data
    $player_id = $_POST['player_id'];
    $amount = $_POST['amount'];
    $payment_date = $_POST['payment_date'];
    
    // Generate a unique payment ID
    $pay_id = 'PAY' . uniqid();
    
    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO tblpayment (PayID, Amount, Date, PID) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdss", $pay_id, $amount, $payment_date, $player_id);
    
    // Execute the statement
    if ($stmt->execute()) {
        // Success - redirect to confirmation
        header("Location: ?page=success&pay_id=" . $pay_id);
        exit();
    } else {
        // Error
        $error = "Error: " . $stmt->error;
    }
    
    // Close statement and connection
    $stmt->close();
    $conn->close();
}

// Determine which page to show
$page = $_GET['page'] ?? 'form';
$playerId = $_SESSION['player_id'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page === 'success' ? 'Payment Successful' : 'Make a Player Payment'; ?></title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f1e9;
            margin: 0;
            padding: 20px;
            color: #5a4a42;
        }
        .container {
            max-width: 500px;
            margin: 30px auto;
            background: #f9f3ee;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(149, 117, 89, 0.1);
            border: 1px solid #e0d1c5;
        }
        h2 {
            text-align: center;
            color: #8b6b4a;
            margin-bottom: 25px;
            font-weight: 600;
        }
        label {
            display: block;
            margin: 15px 0 8px;
            font-weight: 500;
            color: #7a5c44;
        }
        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 18px;
            border: 1px solid #d4c4b8;
            border-radius: 6px;
            box-sizing: border-box;
            background-color: #fdfaf7;
            color: #5a4a42;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #b89b84;
            box-shadow: 0 0 0 2px rgba(184, 155, 132, 0.2);
        }
        input[readonly] {
            background-color: #f0e6dd;
            color: #7a6b5f;
        }
        button, .btn {
            display: block;
            width: 100%;
            padding: 14px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            margin-top: 10px;
            transition: background-color 0.3s;
            text-align: center;
            text-decoration: none;
            box-sizing: border-box;
        }
        button {
            background-color: #b89b84;
            color: white;
        }
        button:hover {
            background-color: #a08670;
        }
        .btn {
            background-color: #f0e6dd;
            color: #7a5c44;
            border: 1px solid #d4c4b8;
        }
        .btn:hover {
            background-color: #e0d1c5;
        }
        #paymentError {
            color: #c17c74;
            margin-top: 15px;
            text-align: center;
            font-size: 14px;
            min-height: 20px;
        }
        .button-group {
            display: flex;
            gap: 10px;
        }
        .button-group button, .button-group .btn {
            width: 100%;
        }
        .success-icon {
            font-size: 60px;
            color: #a08670;
            margin-bottom: 25px;
            display: inline-block;
        }
        .payment-id {
            font-weight: 500;
            color: #7a5c44;
            background: #f0e6dd;
            padding: 8px 12px;
            border-radius: 4px;
            display: inline-block;
            margin: 5px 0;
        }
    </style>
</head>
<body>

<?php if ($page === 'form'): ?>
    <div class="container">
        <h2>Make a Player Payment</h2>
        <?php if (isset($_GET['error'])): ?>
            <p id="paymentError"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>
        <form id="paymentForm" method="POST" onsubmit="return validatePaymentForm()">
            <input type="hidden" name="submit_payment" value="1">
            <label for="player_id">Player ID</label>
            <input type="text" id="player_id" name="player_id" value="<?php echo htmlspecialchars($playerId); ?>" >

            <label for="amount">Amount ($)</label>
            <input type="number" step="0.01" min="0.01" id="amount" name="amount" required placeholder="0.00">

            <label for="payment_date">Payment Date</label>
            <input type="date" id="payment_date" name="payment_date" required>

            <div class="button-group">
                <a href="givepayment.php" class="btn">Back to Schedule</a>
                <button type="submit">Submit Payment</button>
            </div>
        </form>
    </div>

    <script>
    function validatePaymentForm() {
        const amount = document.getElementById('amount').value;
        const paymentDate = document.getElementById('payment_date').value;
        const errorElement = document.getElementById('paymentError');
        
        errorElement.textContent = '';
        
        // Validate amount
        if (amount <= 0) {
            errorElement.textContent = 'Amount must be greater than 0';
            return false;
        }
        
        // Validate date (not in future)
        const today = new Date();
        const selectedDate = new Date(paymentDate);
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate > today) {
            errorElement.textContent = 'Payment date cannot be in the future';
            return false;
        }
        
        return true;
    }
    </script>

<?php elseif ($page === 'success' && isset($_GET['pay_id'])): ?>
    <div class="container">
        <div class="success-icon">✓</div>
        <h2>Payment Successful</h2>
        <p>Your payment has been processed successfully.</p>
        <p>Payment ID: <span class="payment-id"><?php echo htmlspecialchars($_GET['pay_id']); ?></span></p>
        <a href="submit_schedule.php" class="btn">Return to Dashboard</a>
    </div>
<?php endif; ?>

</body>
</html>