<?php
require_once 'vendor/autoload.php';
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'player') {
    header("Location: login4.html");
    exit();
}

\Stripe\Stripe::setApiKey('api key');

$player_id = $_POST['player_id'];
$amount = floatval($_POST['amount']);

if (empty($player_id) || $amount <= 0) {
    header("Location: payment_form.php?error=Invalid input data");
    exit();
}

$amount_cents = round($amount * 100);

$payment_ref = 'PAY' . uniqid();

$_SESSION['payment_ref'] = $payment_ref;
$_SESSION['payment_amount'] = $amount;
$_SESSION['payment_player_id'] = $player_id;

try {
    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => 'Legacy Sports Payment',
                    'description' => 'Payment for Player ID: ' . $player_id,
                ],
                'unit_amount' => $amount_cents,
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/web/submit_payment.php?session_id={CHECKOUT_SESSION_ID}&ref=' . $payment_ref,
        'cancel_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/web/payment_form.php?error=Payment%20was%20cancelled',
        'metadata' => [
            'player_id' => $player_id,
            'payment_ref' => $payment_ref
        ],
    ]);

    header("HTTP/1.1 303 See Other");
    header("Location: " . $checkout_session->url);
    exit();
    
} catch (\Stripe\Exception\ApiErrorException $e) {
    
    header("Location: payment_form.php?error=" . urlencode('Stripe error: ' . $e->getMessage()));
    exit();
} catch (\Exception $e) {
    
    header("Location: payment_form.php?error=" . urlencode('Server error: ' . $e->getMessage()));
    exit();
}