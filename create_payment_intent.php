<?php
require_once 'vendor/autoload.php';

session_start();
header('Content-Type: application/json');


if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'player') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}


$input = json_decode(file_get_contents('php://input'), true);


if (!isset($input['amount']) || !isset($input['player_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit();
}

$amount = intval($input['amount']);
$player_id = $input['player_id'];


\Stripe\Stripe::setApiKey('api key'); // 

try {
 
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $amount,
        'currency' => 'usd',
        'metadata' => [
            'player_id' => $player_id
        ]
    ]);

 
    echo json_encode([
        'client_secret' => $paymentIntent->client_secret,
        'id' => $paymentIntent->id
    ]);
} catch (\Stripe\Exception\ApiErrorException $e) {
  
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} catch (\Exception $e) {
    
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
