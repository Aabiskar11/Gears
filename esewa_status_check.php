<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_uuid = $_POST['transaction_uuid'] ?? '';
    $total_amount = $_POST['total_amount'] ?? '';
    $product_code = 'EPAYTEST';

    if ($transaction_uuid && $total_amount) {
        $url = "https://rc.esewa.com.np/api/epay/transaction/status/?product_code=$product_code&total_amount=$total_amount&transaction_uuid=$transaction_uuid";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);
        if ($result) {
            echo '<h2>eSewa Payment Status</h2>';
            echo '<pre>' . htmlspecialchars(print_r($result, true)) . '</pre>';
        } else {
            echo '<p>Failed to get status from eSewa.</p>';
        }
    } else {
        echo '<p>Missing transaction UUID or amount.</p>';
    }
} else {
    
    echo '<form method="POST">';
    echo '<label>Order Number (transaction_uuid): <input type="text" name="transaction_uuid" required></label><br>';
    echo '<label>Total Amount: <input type="text" name="total_amount" required></label><br>';
    echo '<button type="submit">Check Status</button>';
    echo '</form>';
} 