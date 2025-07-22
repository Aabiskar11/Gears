<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'seller') {
    header("Location: ../login.php");
    exit();
}

// Check if seller is approved
$stmt = $pdo->prepare("SELECT is_approved FROM seller_profiles WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch();

if (!$profile || !$profile['is_approved']) {
    header("Location: pending_approval.php");
    exit();
}

// Get seller statistics
$stats = [];

// Total products
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM products WHERE seller_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$stats['total_products'] = $stmt->fetch()['total'];

// Featured products
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM products WHERE seller_id = ? AND is_featured = TRUE");
$stmt->execute([$_SESSION['user_id']]);
$stats['featured_products'] = $stmt->fetch()['total'];

// Low stock products (less than 10)
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM products WHERE seller_id = ? AND stock_quantity < 10");
$stmt->execute([$_SESSION['user_id']]);
$stats['low_stock'] = $stmt->fetch()['total'];

// Out of stock products
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM products WHERE seller_id = ? AND stock_quantity = 0");
$stmt->execute([$_SESSION['user_id']]);
$stats['out_of_stock'] = $stmt->fetch()['total'];

// Get seller's products
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE p.seller_id = ? 
                       ORDER BY p.created_at DESC 
                       LIMIT 10");
$stmt->execute([$_SESSION['user_id']]);
$recent_products = $stmt->fetchAll();

// Get seller profile
$stmt = $pdo->prepare("SELECT * FROM seller_profiles WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$seller_profile = $stmt->fetch();

// Get orders for this seller's products
$orders = [];
$stmt = $pdo->prepare('
    SELECT o.id as order_id, o.created_at as order_date, oi.quantity, p.name as product_name, u.fullname as buyer_name, u.email as buyer_email
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    JOIN users u ON o.user_id = u.id
    WHERE p.seller_id = ?
    ORDER BY o.created_at DESC
    LIMIT 20
');
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Seller Dashboard - Gears</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background: #f0f2f5;
        }
        
        .header {
            background: #a50c2a;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 24px;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            color: #a50c2a;
            margin-bottom: 0.5rem;
        }
        
        .stat-card .number {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
        }
        
        .section {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .section h2 {
            color: #a50c2a;
            margin-bottom: 1rem;
            border-bottom: 2px solid #f0f2f5;
            padding-bottom: 0.5rem;
        }
        
        .product-item {
            border: 1px solid #e0e0e0;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .product-info h4 {
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .product-info p {
            color: #666;
            margin-bottom: 0.25rem;
        }
        
        .stock-status {
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .stock-ok {
            background: #e8f5e8;
            color: #2e7d32;
        }
        
        .stock-low {
            background: #fff3e0;
            color: #f57c00;
        }
        
        .stock-out {
            background: #ffebee;
            color: #c62828;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            margin-left: 0.5rem;
        }
        
        .btn-primary {
            background: #a50c2a;
            color: white;
        }
        
        .btn-secondary {
            background: #666;
            color: white;
        }
        
        .profile-info {
            background: #f9f9f9;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .profile-info p {
            margin-bottom: 0.5rem;
        }
        
        .success {
            color: #388e3c;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .error {
            color: #d32f2f;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .orders-section {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .orders-section h2 {
            color: #a50c2a;
            margin-bottom: 1rem;
            border-bottom: 2px solid #f0f2f5;
            padding-bottom: 0.5rem;
        }
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }
        .orders-table th, .orders-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .orders-table th {
            background: #a50c2a;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Seller Dashboard</h1>
        <div class="nav-links">
            <a href="add_product.php">Add Product</a>
            <a href="my_products.php">My Products</a>
            <a href="profile.php">Profile</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?php echo htmlspecialchars($_SESSION['success']); ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Products</h3>
                <div class="number"><?php echo $stats['total_products']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Featured Products</h3>
                <div class="number"><?php echo $stats['featured_products']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Low Stock</h3>
                <div class="number"><?php echo $stats['low_stock']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Out of Stock</h3>
                <div class="number"><?php echo $stats['out_of_stock']; ?></div>
            </div>
        </div>
        
        <div class="section">
            <h2>Business Profile</h2>
            <div class="profile-info">
                <p><strong>Business Name:</strong> <?php echo htmlspecialchars($seller_profile['business_name']); ?></p>
                <p><strong>Contact Phone:</strong> <?php echo htmlspecialchars($seller_profile['contact_phone']); ?></p>
                <p><strong>Business Address:</strong> <?php echo htmlspecialchars($seller_profile['business_address']); ?></p>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($seller_profile['business_description']); ?></p>
            </div>
            <a href="profile.php" class="btn btn-primary">Edit Profile</a>
        </div>
        
        <div class="section">
            <h2>Recent Products</h2>
            <?php if (empty($recent_products)): ?>
                <p>No products added yet. <a href="add_product.php">Add your first product</a></p>
            <?php else: ?>
                <?php foreach ($recent_products as $product): ?>
                    <div class="product-item">
                        <div class="product-info">
                            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                            <p><strong>Price:</strong> $<?php echo number_format($product['price'], 2); ?></p>
                            <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category_name']); ?></p>
                            <p><strong>Stock:</strong> <?php echo $product['stock_quantity']; ?> units</p>
                        </div>
                        <div>
                            <?php 
                            if ($product['stock_quantity'] == 0) {
                                echo '<span class="stock-status stock-out">Out of Stock</span>';
                            } elseif ($product['stock_quantity'] < 10) {
                                echo '<span class="stock-status stock-low">Low Stock</span>';
                            } else {
                                echo '<span class="stock-status stock-ok">In Stock</span>';
                            }
                            ?>
                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary">Edit</a>
                        </div>
                    </div>
                <?php endforeach; ?>
                <a href="my_products.php" class="btn btn-primary">View All Products</a>
            <?php endif; ?>
        </div>
        <div class="orders-section">
            <h2>Recent Orders for Your Products</h2>
            <?php if (empty($orders)): ?>
                <p>No orders for your products yet.</p>
            <?php else: ?>
                <table class="orders-table">
                    <tr>
                        <th>Order ID</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Buyer</th>
                        <th>Buyer Email</th>
                        <th>Order Date</th>
                    </tr>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['order_id']) ?></td>
                        <td><?= htmlspecialchars($order['product_name']) ?></td>
                        <td><?= htmlspecialchars($order['quantity']) ?></td>
                        <td><?= htmlspecialchars($order['buyer_name']) ?></td>
                        <td><?= htmlspecialchars($order['buyer_email']) ?></td>
                        <td><?= htmlspecialchars($order['order_date']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 