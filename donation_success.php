<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.html");
    exit();
}

// Check for donation ID in URL
if (!isset($_GET['donation_id'])) {
    header("Location: Donor_donation.php?error=No donation ID provided");
    exit();
}

$donation_id = $_GET['donation_id'];

// Connect to database to get donation details
$host = "localhost";
$user = "root";
$password = "";
$dbname = "legacy_db";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    header("Location: Donor_donation.php?error=Database connection error");
    exit();
}

// Get donation and player information
$donation_details = null;
$stmt = $conn->prepare("SELECT d.*, p.Name AS PlayerName 
                       FROM tbldonation d
                       JOIN tblplayer p ON d.PID = p.PID
                       WHERE d.DID = ?");
$stmt->bind_param("s", $donation_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $donation_details = $row;
}

$stmt->close();
$conn->close();

// If donation not found, redirect
if (!$donation_details) {
    header("Location: Donor_donation.php?error=Donation not found");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Success | Legacy Sports</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f0e1, #e4d6c8);
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .success-container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
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

        .donation-details {
            background-color: #e8f5e9;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 30px;
            text-align: left;
            border: 1px solid #c8e6c9;
        }

        .donation-id {
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
        <h1>Donation Successful!</h1>
        <div class="success-message">
            Thank you for your donation. Your generosity will help support our athletes!
        </div>
        <div class="donation-details">
            <p><strong>Donation ID:</strong> <span class="donation-id"><?php echo htmlspecialchars($donation_id); ?></span></p>
            <p><strong>Player:</strong> <?php echo htmlspecialchars($donation_details['PlayerName'] ?? 'Unknown'); ?></p>
            <p><strong>Amount:</strong> $<?php echo htmlspecialchars(number_format($donation_details['Amount'], 2)); ?></p>
            <p><strong>Date:</strong> <?php echo htmlspecialchars(date('F j, Y', strtotime($donation_details['Date']))); ?></p>
        </div>
        <a href="Donor_donation.php" class="button">Return to Donations</a>
    </div>
</body>

</html>