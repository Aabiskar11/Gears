<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'seller') {
    header("Location: ../login.php");
    exit();
}

// Get seller profile status
$stmt = $pdo->prepare("SELECT * FROM seller_profiles WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pending Approval - Gears</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        
        .icon {
            font-size: 64px;
            color: #ff9800;
            margin-bottom: 20px;
        }
        
        h1 {
            color: #a50c2a;
            margin-bottom: 20px;
            font-size: 24px;
        }
        
        .message {
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .status-box {
            background: #fff3e0;
            border: 1px solid #ff9800;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .status-box h3 {
            color: #e65100;
            margin-bottom: 10px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #a50c2a;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 0 10px;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #8b0a24;
        }
        
        .btn-secondary {
            background: #666;
        }
        
        .btn-secondary:hover {
            background: #555;
        }
        
        .success {
            color: #388e3c;
            font-size: 14px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">⏳</div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?php echo htmlspecialchars($_SESSION['success']); ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <h1>Application Under Review</h1>
        
        <div class="message">
            Thank you for submitting your seller application! Our admin team is currently reviewing your information.
        </div>
        
        <div class="status-box">
            <h3>Application Status</h3>
            <?php if ($profile): ?>
                <p><strong>Business Name:</strong> <?php echo htmlspecialchars($profile['business_name']); ?></p>
                <p><strong>Submitted:</strong> <?php echo date('M j, Y g:i A', strtotime($profile['created_at'])); ?></p>
                <p><strong>Status:</strong> 
                    <?php if ($profile['is_approved']): ?>
                        <span style="color: #4caf50;">✓ Approved</span>
                    <?php else: ?>
                        <span style="color: #ff9800;">⏳ Pending Review</span>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </div>
        
        <div class="message">
            You will receive an email notification once your application has been reviewed. 
            This process typically takes 1-2 business days.
        </div>
        
        <div>
            <a href="../landingpage.php" class="btn btn-secondary">Back to Home</a>
            <a href="../logout.php" class="btn">Logout</a>
        </div>
    </div>
</body>
</html> 