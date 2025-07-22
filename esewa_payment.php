<?php
require_once 'utils/esewa_signature.php';
session_start();


if (isset($_SESSION['esewa_order'])) {
    $order = $_SESSION['esewa_order'];
    unset($_SESSION['esewa_order']);
    $amount = $order['amount'];
    $tax_amount = $order['tax_amount'];
    $total_amount = $order['total_amount'];
    $transaction_uuid = $order['transaction_uuid'];
    $product_code = $order['product_code'];
    $product_service_charge = $order['product_service_charge'];
    $product_delivery_charge = $order['product_delivery_charge'];
    $success_url = $order['success_url'];
    $failure_url = $order['failure_url'];
} else {
   
    $amount = $_POST['amount'];
    $tax_amount = $_POST['tax_amount'];
    $total_amount = $_POST['total_amount'];
    $transaction_uuid = $_POST['transaction_uuid'];
    $product_code = $_POST['product_code'];
    $product_service_charge = $_POST['product_service_charge'];
    $product_delivery_charge = $_POST['product_delivery_charge'];
    $success_url = $_POST['success_url'];
    $failure_url = $_POST['failure_url'];
}


$signed_field_names = 'total_amount,transaction_uuid,product_code';
$data = [
    'total_amount' => $total_amount,
    'transaction_uuid' => $transaction_uuid,
    'product_code' => $product_code
];
$signature = EsewaSignature::generateSignature($data, $signed_field_names);

// eSewa UAT URL
$esewa_url = 'https://rc-epay.esewa.com.np/api/epay/main/v2/form';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Redirecting to eSewa...</title>
</head>
<body onload="document.forms[0].submit()">
    <form action="<?php echo htmlspecialchars($esewa_url); ?>" method="POST">
        <input type="hidden" name="amount" value="<?php echo htmlspecialchars($amount); ?>">
        <input type="hidden" name="tax_amount" value="<?php echo htmlspecialchars($tax_amount); ?>">
        <input type="hidden" name="total_amount" value="<?php echo htmlspecialchars($total_amount); ?>">
        <input type="hidden" name="transaction_uuid" value="<?php echo htmlspecialchars($transaction_uuid); ?>">
        <input type="hidden" name="product_code" value="<?php echo htmlspecialchars($product_code); ?>">
        <input type="hidden" name="product_service_charge" value="<?php echo htmlspecialchars($product_service_charge); ?>">
        <input type="hidden" name="product_delivery_charge" value="<?php echo htmlspecialchars($product_delivery_charge); ?>">
        <input type="hidden" name="success_url" value="<?php echo htmlspecialchars($success_url); ?>">
        <input type="hidden" name="failure_url" value="<?php echo htmlspecialchars($failure_url); ?>">
        <input type="hidden" name="signed_field_names" value="<?php echo htmlspecialchars($signed_field_names); ?>">
        <input type="hidden" name="signature" value="<?php echo htmlspecialchars($signature); ?>">
    </form>
</body>
</html> 