<?php
require_once 'vendor/autoload.php';

session_start();

// if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'player') {
//     header("Location: login4.html");
//     exit();
// }

if (
    !isset($_GET['session_id']) || !isset($_GET['ref']) ||
    !isset($_SESSION['payment_ref']) || $_GET['ref'] !== $_SESSION['payment_ref']
) {
    header("Location: payment_form.php?error=Invalid payment verification");
    exit();
}

\Stripe\Stripe::setApiKey('api key');

try {
    $session = \Stripe\Checkout\Session::retrieve($_GET['session_id']);

    if ($session->payment_status !== 'paid') {
        header("Location: payment_form.php?error=Payment not completed");
        exit();
    }

    $player_id = $_SESSION['payment_player_id'];
    $amount = $_SESSION['payment_amount'];
    $payment_ref = $_SESSION['payment_ref'];
    $payment_date = date('Y-m-d');

    $host = "localhost";
    $user = "root";
    $password = "";
    $dbname = "legacy_db";

    $conn = new mysqli($host, $user, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    $pay_id = 'PAY' . uniqid();

    $stmt = $conn->prepare("INSERT INTO tblpayment (PayID, Amount, Date, PID) VALUES (?, ?, ?, ?)");

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sdss", $pay_id, $amount, $payment_date, $player_id);

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    unset($_SESSION['payment_ref']);
    unset($_SESSION['payment_amount']);
    unset($_SESSION['payment_player_id']);

    $stmt->close();
    $conn->close();

    header("Location: payment_success.php?pay_id=" . $pay_id);
    exit();
} catch (\Stripe\Exception\ApiErrorException $e) {
    header("Location: payment_form.php?error=" . urlencode('Stripe error: ' . $e->getMessage()));
    exit();
} catch (\Exception $e) {
    header("Location: payment_form.php?error=" . urlencode('Server error: ' . $e->getMessage()));
    exit();
}
