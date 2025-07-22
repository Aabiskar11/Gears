<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: login.php');
    exit;
}

// Check if order success data exists
if (!isset($_SESSION['order_success'])) {
    header('Location: product.php');
    exit;
}

$order_data = $_SESSION['order_success'];
$user_name = $_SESSION['user_name'];

// Clear the order success data to prevent refresh issues
unset($_SESSION['order_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Gears</title>
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
        
        /* Success Page Specific Styles */
        .success-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 0 20px;
        }
        .success-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .success-icon {
            width: 80px;
            height: 80px;
            background: #4CAF50;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 40px;
            color: white;
        }
        .success-title {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 15px;
        }
        .success-subtitle {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 30px;
        }
        .order-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 30px;
            margin: 30px 0;
            text-align: left;
        }
        .order-details h3 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .detail-row:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 1.1rem;
            color: crimson;
        }
        .detail-label {
            font-weight: 500;
            color: #555;
        }
        .detail-value {
            color: #333;
        }
        .next-steps {
            background: #e8f5e8;
            border-radius: 10px;
            padding: 25px;
            margin: 30px 0;
        }
        .next-steps h3 {
            color: #2e7d32;
            margin-bottom: 15px;
        }
        .next-steps ul {
            list-style: none;
            padding: 0;
        }
        .next-steps li {
            padding: 8px 0;
            color: #2e7d32;
            position: relative;
            padding-left: 25px;
        }
        .next-steps li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #4CAF50;
            font-weight: bold;
        }
        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
            display: inline-block;
        }
        .btn-primary {
            background: crimson;
            color: white;
        }
        .btn-primary:hover {
            background: #a50c2a;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .btn-outline {
            background: white;
            color: crimson;
            border: 2px solid crimson;
        }
        .btn-outline:hover {
            background: crimson;
            color: white;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .success-card {
                padding: 30px 20px;
            }
            .success-title {
                font-size: 2rem;
            }
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            .btn {
                width: 100%;
                max-width: 300px;
                text-align: center;
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

    <div class="success-container">
        <div class="success-card">
            <div class="success-icon">✓</div>
            <h1 class="success-title">Order Confirmed!</h1>
            <p class="success-subtitle">Thank you for your purchase. Your order has been successfully placed.</p>
            
            <div class="order-details">
                <h3>Order Details</h3>
                <div class="detail-row">
                    <span class="detail-label">Order Number:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order_data['order_number']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Order Date:</span>
                    <span class="detail-value"><?php echo date('F j, Y'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Status:</span>
                    <span class="detail-value">Paid</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Order Status:</span>
                    <span class="detail-value">Processing</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Amount:</span>
                    <span class="detail-value">$<?php echo number_format($order_data['total'], 2); ?></span>
                </div>
            </div>
            
            <div class="next-steps">
                <h3>What's Next?</h3>
                <ul>
                    <li>You will receive an order confirmation email shortly</li>
                    <li>Our team will process your order within 1-2 business days</li>
                    <li>You'll receive shipping updates via email</li>
                    <li>Track your order status in your account dashboard</li>
                </ul>
            </div>
            
            <div class="action-buttons">
                <a href="product.php" class="btn btn-primary">Continue Shopping</a>
                <a href="orders.php" class="btn btn-secondary">View My Orders</a>
                <a href="landingpage.php" class="btn btn-outline">Back to Home</a>
            </div>
        </div>
    </div>

    <script>
        // Auto-redirect to products page after 30 seconds
        setTimeout(function() {
            if (confirm('Would you like to continue shopping?')) {
                window.location.href = 'product.php';
            }
        }, 30000);
    </script>
</body>
</html> 