<?php
require_once 'vendor/autoload.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.html");
    exit();
}

// Verify the session parameters and GET parameters
if (
    !isset($_GET['session_id']) || !isset($_GET['ref']) ||
    !isset($_SESSION['donation_ref']) || $_GET['ref'] !== $_SESSION['donation_ref'] ||
    !isset($_SESSION['donation_amount']) || !isset($_SESSION['donation_player_id']) || !isset($_SESSION['donation_donor_id'])
) {
    header("Location: Donor_donation.php?error=Invalid donation verification");
    exit();
}

\Stripe\Stripe::setApiKey('api key');

try {
    // Verify the payment with Stripe
    $session = \Stripe\Checkout\Session::retrieve($_GET['session_id']);

    // Check if payment was successful
    if ($session->payment_status !== 'paid') {
        header("Location: Donor_donation.php?error=Payment not completed");
        exit();
    }

    // Get the donation details from session
    $player_id = $_SESSION['donation_player_id'];
    $amount = $_SESSION['donation_amount'];
    $donor_id = $_SESSION['donation_donor_id'];
    $donation_ref = $_SESSION['donation_ref'];
    $payment_date = date('Y-m-d');

    // Connect to database
    $host = "localhost";
    $user = "root";
    $password = "";
    $dbname = "legacy_db";

    $conn = new mysqli($host, $user, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Generate a unique donation ID
    $donation_id = 'DON' . uniqid();

    // Insert donation into database
    $stmt = $conn->prepare("INSERT INTO tbldonation (DID, Date, Amount, PID, SDID) VALUES (?, ?, ?, ?, ?)");

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssdss", $donation_id, $payment_date, $amount, $player_id, $donor_id);

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    // Clear session variables
    unset($_SESSION['donation_ref']);
    unset($_SESSION['donation_amount']);
    unset($_SESSION['donation_player_id']);
    unset($_SESSION['donation_donor_id']);

    $stmt->close();
    $conn->close();

    // Redirect to success page
    header("Location: donation_success.php?donation_id=" . $donation_id);
    exit();
} catch (\Stripe\Exception\ApiErrorException $e) {
    header("Location: Donor_donation.php?error=" . urlencode('Stripe error: ' . $e->getMessage()));
    exit();
} catch (\Exception $e) {
    header("Location: Donor_donation.php?error=" . urlencode('Server error: ' . $e->getMessage()));
    exit();
}
