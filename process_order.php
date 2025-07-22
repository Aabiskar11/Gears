<?php
session_start();
require_once 'config/database.php';

// Debug: print session and workflow steps if ?debug=1
$debug = isset($_GET['debug']);
if ($debug) {
    echo '<pre>process_order.php: $_SESSION: ';
    print_r($_SESSION);
    echo '</pre>';
}

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    if ($debug) echo '<b>Not logged in, redirecting to login.php</b><br>';
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($debug) echo '<b>Not POST, redirecting to checkout.php</b><br>';
    header('Location: checkout.php');
    exit;
}

// Validate cart has items
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart_count = $stmt->fetch()['count'];
    if ($debug) echo "Cart count: $cart_count<br>";
    
    if ($cart_count == 0) {
        $_SESSION['error'] = 'Your cart is empty.';
        if ($debug) echo '<b>Cart is empty, redirecting to cart.php</b><br>';
        header('Location: cart.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error occurred.';
    if ($debug) echo '<b>Database error, redirecting to checkout.php</b><br>';
    header('Location: checkout.php');
    exit;
}

// Validate required fields
$required_fields = [
    'shipping_first_name', 'shipping_last_name', 'shipping_address_line1',
    'shipping_city', 'shipping_state', 'shipping_postal_code', 'shipping_country',
    'shipping_phone', 'payment_method'
];

foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['error'] = 'Please fill in all required fields.';
        header('Location: checkout.php');
        exit;
    }
}

// Prepare addresses
$shipping_address = formatAddress($_POST, 'shipping');
$billing_address = $_POST['same_as_shipping'] ? $shipping_address : formatAddress($_POST, 'billing');

$payment_method = $_POST['payment_method'];

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Generate order number
    $order_number = generateOrderNumber();
    
    // Calculate total
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(c.quantity * p.price), 0) as total_amount
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $total_amount = $stmt->fetch()['total_amount'];
    $tax_amount = $total_amount * 0.08;
    $final_total = $total_amount + $tax_amount;
    
    // Create order
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, order_number, total_amount, shipping_address, billing_address, payment_method)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $order_number, $final_total, $shipping_address, $billing_address, $payment_method]);
    $order_id = $pdo->lastInsertId();
    
    // Add order items
    $stmt = $pdo->prepare("
        SELECT c.product_id, c.quantity, p.name, p.price
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();
    
    $order_items_stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, subtotal)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($cart_items as $item) {
        $subtotal = $item['quantity'] * $item['price'];
        $order_items_stmt->execute([
            $order_id,
            $item['product_id'],
            $item['name'],
            $item['price'],
            $item['quantity'],
            $subtotal
        ]);
    }
    
    // Clear cart
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    $pdo->commit();

    if ($payment_method === 'esewa') {
        $_SESSION['esewa_order'] = [
            'amount' => number_format($total_amount, 2, '.', ''),
            'tax_amount' => number_format($tax_amount, 2, '.', ''),
            'total_amount' => number_format($final_total, 2, '.', ''),
            'transaction_uuid' => $order_number,
            'product_code' => 'EPAYTEST',
            'product_service_charge' => 0,
            'product_delivery_charge' => 0,
            'success_url' => 'http://localhost/Gears/esewa_success.php',
            'failure_url' => 'http://localhost/Gears/esewa_failure.php'
        ];
        if ($debug) {
            echo '<b>Set $_SESSION[\'esewa_order\']:</b><br>';
            print_r($_SESSION['esewa_order']);
        }
        header('Location: esewa_payment.php?debug=1');
        exit;
    }

    // Process payment based on method (credit_card, paypal)
    $payment_result = processPayment($order_id, $final_total, $payment_method, $_POST);
    
    if ($payment_result['success']) {
        // Update order status
        $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'paid', status = 'processing' WHERE id = ?");
        $stmt->execute([$order_id]);
        
        // Redirect to success page
        $_SESSION['order_success'] = [
            'order_number' => $order_number,
            'order_id' => $order_id,
            'total' => $final_total
        ];
        header('Location: order_success.php');
        exit;
    } else {
        // Payment failed
        $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'failed' WHERE id = ?");
        $stmt->execute([$order_id]);
        
        $_SESSION['error'] = 'Payment failed: ' . $payment_result['message'];
        header('Location: checkout.php');
        exit;
    }
    
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'An error occurred while processing your order.';
    header('Location: checkout.php');
    exit;
}

function formatAddress($data, $prefix) {
    $address = $data[$prefix . '_first_name'] . ' ' . $data[$prefix . '_last_name'] . "\n";
    
    if (!empty($data[$prefix . '_company'])) {
        $address .= $data[$prefix . '_company'] . "\n";
    }
    
    $address .= $data[$prefix . '_address_line1'] . "\n";
    
    if (!empty($data[$prefix . '_address_line2'])) {
        $address .= $data[$prefix . '_address_line2'] . "\n";
    }
    
    $address .= $data[$prefix . '_city'] . ', ' . $data[$prefix . '_state'] . ' ' . $data[$prefix . '_postal_code'] . "\n";
    $address .= $data[$prefix . '_country'] . "\n";
    $address .= $data[$prefix . '_phone'];
    
    return $address;
}

function generateOrderNumber() {
    $timestamp = date('YmdHis');
    $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    return 'ORD-' . $timestamp . '-' . $random;
}

function processPayment($order_id, $amount, $payment_method, $payment_data) {
    
    switch ($payment_method) {
        case 'credit_card':
            return processCreditCardPayment($order_id, $amount, $payment_data);
        case 'paypal':
            return processPayPalPayment($order_id, $amount, $payment_data);
        default:
            return ['success' => false, 'message' => 'Invalid payment method'];
    }
}

function processCreditCardPayment($order_id, $amount, $payment_data) {
    
    $card_number = $payment_data['card_number'] ?? '';
    $expiry_date = $payment_data['expiry_date'] ?? '';
    $cvv = $payment_data['cvv'] ?? '';
    $card_holder_name = $payment_data['card_holder_name'] ?? '';
    
    if (empty($card_number) || empty($expiry_date) || empty($cvv) || empty($card_holder_name)) {
        return ['success' => false, 'message' => 'Please provide all credit card details'];
    }
    
    // Simulate payment processing delay
    sleep(1);
    
    // Simulate success (90% success rate for demo)
    $success = (rand(1, 100) <= 90);
    
    if ($success) {
        // Generate transaction ID
        $transaction_id = 'TXN-' . date('YmdHis') . '-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
        // In a real application, you would save the transaction details
        // For demo purposes, we'll just return success
        
        return [
            'success' => true,
            'transaction_id' => $transaction_id,
            'message' => 'Payment processed successfully'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Payment declined. Please check your card details and try again.'
        ];
    }
}

function processPayPalPayment($order_id, $amount, $payment_data) {
    y
    sleep(1);
    
    
    $success = (rand(1, 100) <= 95);
    
    if ($success) {
        // Generate transaction ID
        $transaction_id = 'PP-' . date('YmdHis') . '-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
        return [
            'success' => true,
            'transaction_id' => $transaction_id,
            'message' => 'PayPal payment processed successfully'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'PayPal payment failed. Please try again.'
        ];
    }
}
?> 