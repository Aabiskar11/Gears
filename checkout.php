<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$cart_items = [];
$cart_summary = [
    'item_count' => 0,
    'total_quantity' => 0,
    'total_amount' => 0
];


$user_addresses = [];

try {
    
    $stmt = $pdo->prepare("
        SELECT 
            c.id as cart_id,
            c.quantity,
            p.id as product_id,
            p.name as product_name,
            p.price,
            p.image_url,
            p.stock_quantity,
            (c.quantity * p.price) as subtotal
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();
    
    
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(c.id) as item_count,
            COALESCE(SUM(c.quantity), 0) as total_quantity,
            COALESCE(SUM(c.quantity * p.price), 0) as total_amount
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_summary = $stmt->fetch();
    
    // Get user addresses
    $stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
    $stmt->execute([$user_id]);
    $user_addresses = $stmt->fetchAll();
    
} 


if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Gears</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background-color: #f9f9f9;
            color: #333;
        }
        .Navbar {
            background: crimson;
            font-family: calibri;
            padding-right: 15px;
            padding-left: 15px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .Navdiv {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
        }
        .logo a {
            font-size: 35px;
            font-weight: 600;
            color: white;
        }        
        li {
            list-style: none;
            display: inline-block;
        }
        li a {
            color: white;
            font-size: 20px;
            font-weight: bold;
            margin-right: 25px;
            transition: 0.3s;
        }
        li a:hover {
            color: #ddd;
        }
        button {
            background-color: white;
            margin-left: 10px;
            border-radius: 15px;
            padding: 10px;
            width: 90px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background-color: #f0f0f0;
        }
        button a {
            color: crimson;
            font-weight: bold;
            font-size: 15px;
        }
        .user-info {
            color: white;
            font-size: 16px;
            margin-right: 15px;
        }
        .logout-btn {
            background-color: #a50c2a !important;
        }
        .logout-btn a {
            color: white !important;
        }
        
        /* Checkout Page Specific Styles */
        .checkout-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .checkout-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .checkout-header h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 10px;
        }
        .checkout-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        .checkout-form {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-section {
            margin-bottom: 30px;
        }
        .form-section h3 {
            font-size: 1.3rem;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 15px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border 0.3s;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: crimson;
            outline: none;
        }
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        .address-options {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .address-option {
            flex: 1;
            padding: 15px;
            border: 2px solid #eee;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }
        .address-option:hover {
            border-color: crimson;
        }
        .address-option.selected {
            border-color: crimson;
            background: #fff5f5;
        }
        .address-option h4 {
            margin-bottom: 10px;
            color: #333;
        }
        .address-option p {
            font-size: 14px;
            color: #666;
            line-height: 1.4;
        }
        .payment-methods {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .payment-method {
            padding: 15px;
            border: 2px solid #eee;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
            text-align: center;
        }
        .payment-method:hover {
            border-color: crimson;
        }
        .payment-method.selected {
            border-color: crimson;
            background: #fff5f5;
        }
        .payment-method input[type="radio"] {
            margin-right: 10px;
        }
        .order-summary {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content;
        }
        .summary-title {
            font-size: 1.3rem;
            margin-bottom: 20px;
            color: #333;
        }
        .order-items {
            margin-bottom: 20px;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .item-info {
            flex: 1;
        }
        .item-name {
            font-weight: 500;
            margin-bottom: 5px;
        }
        .item-details {
            font-size: 14px;
            color: #666;
        }
        .item-price {
            font-weight: bold;
            color: #333;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .summary-row:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 1.2rem;
            color: crimson;
        }
        .place-order-btn {
            width: 100%;
            background: crimson;
            color: white;
            padding: 15px;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 20px;
        }
        .place-order-btn:hover {
            background: #a50c2a;
        }
        .place-order-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .checkout-content {
                grid-template-columns: 1fr;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
            .payment-methods {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="Navbar">
        <div class="Navdiv">
            <div class="logo"><a href="index.php">Gears</a></div>
            <ul>
                <li><a href="landingpage.php">Home</a></li>
                <li><a href="product.php">Shop</a></li>
                <li><a href="landingpage.php#categories">Categories</a></li>
                <li><a href="#">About</a></li>
                <li><a href="#">Contact</a></li>
                <span class="user-info">Welcome, <?php echo htmlspecialchars($user_name); ?>!</span>
                <button class="logout-btn"><a href="logout.php">Logout</a></button>
            </ul>
        </div>
    </nav>

    <div class="checkout-container">
        <div class="checkout-header">
            <h1>Checkout</h1>
            <p>Complete your purchase</p>
        </div>

        <form id="checkout-form" method="POST" action="process_order.php">
            <div class="checkout-content">
                <div class="checkout-form">
                    <!-- Shipping Address Section -->
                    <div class="form-section">
                        <h3>Shipping Address</h3>
                        <?php if (!empty($user_addresses)): ?>
                            <div class="address-options">
                                <?php foreach ($user_addresses as $address): ?>
                                    <div class="address-option" onclick="selectAddress('shipping', <?php echo $address['id']; ?>)">
                                        <h4><?php echo htmlspecialchars($address['first_name'] . ' ' . $address['last_name']); ?></h4>
                                        <p>
                                            <?php echo htmlspecialchars($address['address_line1']); ?><br>
                                            <?php if ($address['address_line2']): ?>
                                                <?php echo htmlspecialchars($address['address_line2']); ?><br>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($address['city'] . ', ' . $address['state'] . ' ' . $address['postal_code']); ?><br>
                                            <?php echo htmlspecialchars($address['country']); ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="shipping_first_name">First Name *</label>
                                <input type="text" id="shipping_first_name" name="shipping_first_name" value="" required>
                            </div>
                            <div class="form-group">
                                <label for="shipping_last_name">Last Name *</label>
                                <input type="text" id="shipping_last_name" name="shipping_last_name" value="" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="shipping_company">Company (Optional)</label>
                            <input type="text" id="shipping_company" name="shipping_company" value="">
                        </div>
                        <div class="form-group">
                            <label for="shipping_address_line1">Address Line 1 *</label>
                            <input type="text" id="shipping_address_line1" name="shipping_address_line1" value="" required>
                        </div>
                        <div class="form-group">
                            <label for="shipping_address_line2">Address Line 2 (Optional)</label>
                            <input type="text" id="shipping_address_line2" name="shipping_address_line2" value="">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="shipping_city">City *</label>
                                <input type="text" id="shipping_city" name="shipping_city" value="" required>
                            </div>
                            <div class="form-group">
                                <label for="shipping_state">State *</label>
                                <input type="text" id="shipping_state" name="shipping_state" value="" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="shipping_postal_code">Postal Code *</label>
                                <input type="text" id="shipping_postal_code" name="shipping_postal_code" value="" required>
                            </div>
                            <div class="form-group">
                                <label for="shipping_country">Country *</label>
                                <select id="shipping_country" name="shipping_country" required>
                                    <option value="">Select Country</option>
                                    <option value="Nepal">Nepal</option>
                                    <option value="India">India</option>
                                    <option value="China">China</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="shipping_phone">Phone *</label>
                            <input type="tel" id="shipping_phone" name="shipping_phone" value="" required>
                        </div>
                    </div>

                    <!-- Billing Address Section -->
                    <div class="form-section">
                        <h3>Billing Address</h3>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="same_as_shipping" onchange="toggleBillingAddress()">
                                Same as shipping address
                            </label>
                        </div>
                        
                        <div id="billing-address-fields">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="billing_first_name">First Name *</label>
                                    <input type="text" id="billing_first_name" name="billing_first_name" value="" required>
                                </div>
                                <div class="form-group">
                                    <label for="billing_last_name">Last Name *</label>
                                    <input type="text" id="billing_last_name" name="billing_last_name" value="" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="billing_company">Company (Optional)</label>
                                <input type="text" id="billing_company" name="billing_company" value="">
                            </div>
                            <div class="form-group">
                                <label for="billing_address_line1">Address Line 1 *</label>
                                <input type="text" id="billing_address_line1" name="billing_address_line1" value="" required>
                            </div>
                            <div class="form-group">
                                <label for="billing_address_line2">Address Line 2 (Optional)</label>
                                <input type="text" id="billing_address_line2" name="billing_address_line2" value="">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="billing_city">City *</label>
                                    <input type="text" id="billing_city" name="billing_city" value="" required>
                                </div>
                                <div class="form-group">
                                    <label for="billing_state">State *</label>
                                    <input type="text" id="billing_state" name="billing_state" value="" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="billing_postal_code">Postal Code *</label>
                                    <input type="text" id="billing_postal_code" name="billing_postal_code" value="" required>
                                </div>
                                <div class="form-group">
                                    <label for="billing_country">Country *</label>
                                    <select id="billing_country" name="billing_country" required>
                                        <option value="">Select Country</option>
                                        <option value="Nepal">Nepal</option>
                                        <option value="India">India</option>
                                        <option value="China">China</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method Section -->
                    <div class="form-section">
                        <h3>Payment Method</h3>
                        <div class="payment-methods">
                            <div class="payment-method" onclick="selectPaymentMethod('credit_card')">
                                <input type="radio" name="payment_method" value="credit_card" id="credit_card" required>
                                <label for="credit_card">Credit Card</label>
                            </div>
                            <div class="payment-method" onclick="selectPaymentMethod('paypal')">
                                <input type="radio" name="payment_method" value="paypal" id="paypal">
                                <label for="paypal">PayPal</label>
                            </div>
                            <div class="payment-method" onclick="selectPaymentMethod('esewa')">
                                <input type="radio" name="payment_method" value="esewa" id="esewa">
                                <label for="esewa">eSewa</label>
                            </div>
                        </div>
                        
                        <div id="credit-card-fields" style="display: none;">
                            <div class="form-group">
                                <label for="card_number">Card Number *</label>
                                <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="expiry_date">Expiry Date *</label>
                                    <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY">
                                </div>
                                <div class="form-group">
                                    <label for="cvv">CVV *</label>
                                    <input type="text" id="cvv" name="cvv" placeholder="123">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="card_holder_name">Cardholder Name *</label>
                                <input type="text" id="card_holder_name" name="card_holder_name">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="order-summary">
                    <h3 class="summary-title">Order Summary</h3>
                    <div class="order-items">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="order-item">
                                <div class="item-info">
                                    <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                    <div class="item-details">Qty: <?php echo $item['quantity']; ?></div>
                                </div>
                                <div class="item-price">$<?php echo number_format($item['subtotal'], 2); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($cart_summary['total_amount'], 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span>Free</span>
                    </div>
                    <div class="summary-row">
                        <span>Tax:</span>
                        <span>$<?php echo number_format($cart_summary['total_amount'] * 0.08, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Total:</span>
                        <span>$<?php echo number_format($cart_summary['total_amount'] * 1.08, 2); ?></span>
                    </div>
                    
                    <button type="submit" class="place-order-btn" id="place-order-btn">
                        Place Order
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function selectAddress(type, addressId) {
            // Remove selected class from all address options
            document.querySelectorAll('.address-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            
            event.currentTarget.classList.add('selected');
            
        }

        function toggleBillingAddress() {
            const sameAsShipping = document.getElementById('same_as_shipping').checked;
            const billingFields = document.getElementById('billing-address-fields');
            
            if (sameAsShipping) {
                billingFields.style.display = 'none';
                // Copy shipping address to billing fields
                copyShippingToBilling();
            } else {
                billingFields.style.display = 'block';
            }
        }

        function copyShippingToBilling() {
            const fields = ['first_name', 'last_name', 'company', 'address_line1', 'address_line2', 'city', 'state', 'postal_code', 'country'];
            
            fields.forEach(field => {
                const shippingField = document.getElementById(`shipping_${field}`);
                const billingField = document.getElementById(`billing_${field}`);
                if (shippingField && billingField) {
                    billingField.value = shippingField.value;
                }
            });
        }

        function selectPaymentMethod(method) {
            // Remove selected class from all payment methods
            document.querySelectorAll('.payment-method').forEach(method => {
                method.classList.remove('selected');
            });
            
            // Add selected class to clicked method
            event.currentTarget.classList.add('selected');
            
            // Show/hide credit card fields
            const creditCardFields = document.getElementById('credit-card-fields');
            if (method === 'credit_card') {
                creditCardFields.style.display = 'block';
            } else {
                creditCardFields.style.display = 'none';
            }
        }

        // Form validation
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Basic validation
            const requiredFields = document.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'red';
                } else {
                    field.style.borderColor = '#ddd';
                }
            });
            
            if (!isValid) {
                alert('Please fill in all required fields.');
                return;
            }
            
            // Submit form
            this.submit();
        });
    </script>
</body>
</html> 