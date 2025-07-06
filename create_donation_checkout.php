<?php
require_once 'vendor/autoload.php';
session_start();

// Check if donor is logged in
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.html");
    exit();
}

\Stripe\Stripe::setApiKey('api key');

// Get donation details from session
if (!isset($_SESSION['donation_amount']) || !isset($_SESSION['donation_player_id']) || !isset($_SESSION['donation_donor_id'])) {
    header("Location: Donor_donation.php?error=Missing donation information");
    exit();
}

$donor_id = $_SESSION['donation_donor_id'];
$player_id = $_SESSION['donation_player_id'];
$amount = floatval($_SESSION['donation_amount']);

if ($amount <= 0) {
    header("Location: Donor_donation.php?error=Invalid donation amount");
    exit();
}

// Convert amount to cents for Stripe
$amount_cents = round($amount * 100);

// Generate a unique reference ID for this donation
$donation_ref = 'DON' . uniqid();

// Store the reference in session for verification after payment
$_SESSION['donation_ref'] = $donation_ref;

try {
    // Get player name for the description
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "legacy_db";

    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    $player_name = "Player";
    $player_query = $conn->prepare("SELECT Name FROM tblplayer WHERE PID = ?");
    $player_query->bind_param("s", $player_id);
    $player_query->execute();
    $result = $player_query->get_result();

    if ($row = $result->fetch_assoc()) {
        $player_name = $row['Name'];
    }

    $conn->close();

    // Create Stripe checkout session
    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => 'Donation to ' . $player_name,
                    'description' => 'Legacy Sports Donation for Player ID: ' . $player_id,
                ],
                'unit_amount' => $amount_cents,
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/web/process_donation.php?session_id={CHECKOUT_SESSION_ID}&ref=' . $donation_ref,
        'cancel_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/web/Donor_donation.php?error=Donation%20was%20cancelled',
        'metadata' => [
            'donor_id' => $donor_id,
            'player_id' => $player_id,
            'donation_ref' => $donation_ref
        ],
    ]);

    // Redirect to Stripe Checkout
    header("HTTP/1.1 303 See Other");
    header("Location: " . $checkout_session->url);
    exit();
} catch (\Stripe\Exception\ApiErrorException $e) {
    header("Location: Donor_donation.php?error=" . urlencode('Stripe error: ' . $e->getMessage()));
    exit();
} catch (\Exception $e) {
    header("Location: Donor_donation.php?error=" . urlencode('Server error: ' . $e->getMessage()));
    exit();
}
