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

// Get cart data
$cart_items = [];
$cart_summary = [
    'item_count' => 0,
    'total_quantity' => 0,
    'total_amount' => 0
];

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
    
    // Calculate cart summary
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
    
} catch (PDOException $e) {
    // Handle error silently
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Gears</title>
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
        
        /* Cart Page Specific Styles */
        .cart-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .cart-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .cart-header h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 10px;
        }
        .cart-empty {
            text-align: center;
            padding: 50px 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .cart-empty h2 {
            color: #666;
            margin-bottom: 20px;
        }
        .cart-empty a {
            display: inline-block;
            background: crimson;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            transition: 0.3s;
        }
        .cart-empty a:hover {
            background: #a50c2a;
        }
        .cart-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        .cart-items {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .cart-item {
            display: grid;
            grid-template-columns: 100px 2fr 1fr 1fr auto;
            gap: 20px;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }
        .cart-item:last-child {
            border-bottom: none;
        }
        .item-image {
            width: 100px;
            height: 100px;
            background: #f5f5f5;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .item-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }
        .item-details h3 {
            margin-bottom: 5px;
            color: #333;
        }
        .item-price {
            font-size: 1.2rem;
            font-weight: bold;
            color: crimson;
        }
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .quantity-btn {
            width: 30px;
            height: 30px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .quantity-btn:hover {
            background: #f5f5f5;
        }
        .quantity-input {
            width: 50px;
            height: 30px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        .item-subtotal {
            font-size: 1.1rem;
            font-weight: bold;
            color: #333;
        }
        .remove-btn {
            background: #ff4444;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
        }
        .remove-btn:hover {
            background: #cc0000;
        }
        .cart-summary {
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
        .checkout-btn {
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
        .checkout-btn:hover {
            background: #a50c2a;
        }
        .checkout-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .continue-shopping {
            text-align: center;
            margin-top: 20px;
        }
        .continue-shopping a {
            color: crimson;
            text-decoration: none;
        }
        .continue-shopping a:hover {
            text-decoration: underline;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .cart-content {
                grid-template-columns: 1fr;
            }
            .cart-item {
                grid-template-columns: 80px 1fr;
                gap: 15px;
            }
            .item-price, .quantity-controls, .item-subtotal, .remove-btn {
                grid-column: 2;
            }
            .quantity-controls {
                justify-content: flex-start;
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

    <div class="cart-container">
        <div class="cart-header">
            <h1>Shopping Cart</h1>
            <p>Review your items and proceed to checkout</p>
        </div>

        <?php if (empty($cart_items)): ?>
            <div class="cart-empty">
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added any items to your cart yet.</p>
                <a href="product.php">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <div class="cart-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item" data-cart-id="<?php echo $item['cart_id']; ?>">
                            <div class="item-image">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                            </div>
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['product_name']); ?></h3>
                                <p>Stock: <?php echo $item['stock_quantity']; ?> available</p>
                            </div>
                            <div class="item-price">$<?php echo number_format($item['price'], 2); ?></div>
                            <div class="quantity-controls">
                                <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, -1)">-</button>
                                <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock_quantity']; ?>" onchange="updateQuantity(<?php echo $item['cart_id']; ?>, this.value, true)">
                                <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, 1)">+</button>
                            </div>
                            <div class="item-subtotal">$<?php echo number_format($item['subtotal'], 2); ?></div>
                            <button class="remove-btn" onclick="removeFromCart(<?php echo $item['cart_id']; ?>)">Remove</button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <h3 class="summary-title">Order Summary</h3>
                    <div class="summary-row">
                        <span>Items (<?php echo $cart_summary['item_count']; ?>):</span>
                        <span><?php echo $cart_summary['total_quantity']; ?></span>
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
                    
                    <button class="checkout-btn" onclick="proceedToCheckout()">
                        Proceed to Checkout
                    </button>
                    
                    <div class="continue-shopping">
                        <a href="product.php">Continue Shopping</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function updateQuantity(cartId, change, isDirectInput = false) {
            let quantity;
            if (isDirectInput) {
                quantity = parseInt(change);
            } else {
                const input = document.querySelector(`[data-cart-id="${cartId}"] .quantity-input`);
                quantity = parseInt(input.value) + parseInt(change);
            }
            
            if (quantity < 1) return;
            
            fetch('cart_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update&cart_id=${cartId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Error updating cart');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating cart');
            });
        }

        function removeFromCart(cartId) {
            if (!confirm('Are you sure you want to remove this item from your cart?')) {
                return;
            }
            
            fetch('cart_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=remove&cart_id=${cartId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Error removing item');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error removing item');
            });
        }

        function proceedToCheckout() {
            window.location.href = 'checkout.php';
        }
    </script>
</body>
</html> 