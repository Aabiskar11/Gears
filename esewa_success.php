<?php
require_once 'utils/esewa_signature.php';
require_once 'config/database.php';

// eSewa sends Base64-encoded response in POST or GET (check both)
$encoded = $_POST['data'] ?? $_GET['data'] ?? null;
if (!$encoded) {
    die('Invalid response from eSewa.');
}

// Decode Base64
$json = base64_decode($encoded);
$response = json_decode($json, true);
if (!$response) {
    die('Failed to decode eSewa response.');
}

// Verify signature
$signed_field_names = $response['signed_field_names'] ?? '';
$signature = $response['signature'] ?? '';
if (!EsewaSignature::verifySignature($response, $signed_field_names, $signature)) {
    die('Signature verification failed.');
}

// Extract order info
$order_number = $response['transaction_uuid'];
$total_amount = $response['total_amount'];
$status = $response['status'];
$transaction_code = $response['transaction_code'] ?? '';


$stmt = $pdo->prepare('UPDATE orders SET payment_status = ? WHERE order_number = ?');
$payment_status = ($status === 'COMPLETE') ? 'paid' : 'failed';
$stmt->execute([$payment_status, $order_number]);

$stmt2 = $pdo->prepare('INSERT INTO payment_transactions (order_id, transaction_id, amount, payment_method, status, gateway_response) SELECT id, ?, ?, ?, ?, ? FROM orders WHERE order_number = ? ON DUPLICATE KEY UPDATE status = VALUES(status), gateway_response = VALUES(gateway_response)');
$stmt2->execute([
    $transaction_code,
    $total_amount,
    'esewa',
    strtolower($status),
    $json,
    $order_number
]);


if ($status === 'COMPLETE') {
    echo '<h2>Payment Successful!</h2><p>Your payment via eSewa was successful. Order: ' . htmlspecialchars($order_number) . '</p>';
} else {
    echo '<h2>Payment Failed</h2><p>Status: ' . htmlspecialchars($status) . '</p>';
} 