<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get statistics
$stats = [];

// Total users
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$stats['total_users'] = $stmt->fetch()['total'];

// Total sellers
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'seller'");
$stats['total_sellers'] = $stmt->fetch()['total'];

// Pending seller approvals
$stmt = $pdo->query("SELECT COUNT(*) as total FROM seller_profiles WHERE is_approved = FALSE");
$stats['pending_sellers'] = $stmt->fetch()['total'];

// Total products
$stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
$stats['total_products'] = $stmt->fetch()['total'];

// Recent admin actions
$stmt = $pdo->query("SELECT aa.*, u.fullname as admin_name FROM admin_actions aa 
                     JOIN users u ON aa.admin_id = u.id 
                     ORDER BY aa.created_at DESC LIMIT 10");
$recent_actions = $stmt->fetchAll();

// Pending seller applications
$stmt = $pdo->query("SELECT sp.*, u.fullname, u.email FROM seller_profiles sp 
                     JOIN users u ON sp.user_id = u.id 
                     WHERE sp.is_approved = FALSE 
                     ORDER BY sp.created_at DESC");
$pending_sellers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Gears</title>
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
        
        .seller-item {
            border: 1px solid #e0e0e0;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
        }
        
        .seller-item h4 {
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .seller-item p {
            color: #666;
            margin-bottom: 0.5rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        
        .btn-approve {
            background: #4caf50;
            color: white;
        }
        
        .btn-reject {
            background: #f44336;
            color: white;
        }
        
        .btn-view {
            background: #2196f3;
            color: white;
        }
        
        .action-log {
            background: #f9f9f9;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .action-log .time {
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Admin Dashboard</h1>
        <div class="nav-links">
            <a href="manage_sellers.php">Manage Sellers</a>
            <a href="manage_products.php">Manage Products</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="number"><?php echo $stats['total_users']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Sellers</h3>
                <div class="number"><?php echo $stats['total_sellers']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Pending Approvals</h3>
                <div class="number"><?php echo $stats['pending_sellers']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Products</h3>
                <div class="number"><?php echo $stats['total_products']; ?></div>
            </div>
        </div>
        
        <div class="section">
            <h2>Pending Seller Approvals</h2>
            <?php if (empty($pending_sellers)): ?>
                <p>No pending seller approvals.</p>
            <?php else: ?>
                <?php foreach ($pending_sellers as $seller): ?>
                    <div class="seller-item">
                        <h4><?php echo htmlspecialchars($seller['business_name']); ?></h4>
                        <p><strong>Owner:</strong> <?php echo htmlspecialchars($seller['fullname']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($seller['email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($seller['contact_phone']); ?></p>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($seller['business_description']); ?></p>
                        <div class="action-buttons">
                            <a href="approve_seller.php?id=<?php echo $seller['user_id']; ?>" class="btn btn-approve">Approve</a>
                            <a href="reject_seller.php?id=<?php echo $seller['user_id']; ?>" class="btn btn-reject">Reject</a>
                            <a href="view_seller.php?id=<?php echo $seller['user_id']; ?>" class="btn btn-view">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2>Recent Admin Actions</h2>
            <?php if (empty($recent_actions)): ?>
                <p>No recent actions.</p>
            <?php else: ?>
                <?php foreach ($recent_actions as $action): ?>
                    <div class="action-log">
                        <div><strong><?php echo htmlspecialchars($action['admin_name']); ?></strong> 
                             <?php echo htmlspecialchars($action['action_type']); ?>
                        </div>
                        <div class="time"><?php echo date('M j, Y g:i A', strtotime($action['created_at'])); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 