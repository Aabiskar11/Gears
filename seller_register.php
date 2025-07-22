<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'customer') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Become a Seller - Gears</title>
        <style>
       *{
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Arial',sans-serif;
       }
       body{
        background: #f0f2f5;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
       }
       .registration-form{
        background: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 500px;
       }
       .registration-form h1{
        text-align: center;
        margin-bottom: 20px;
        color: #a50c2a;
        font-size: 25px;
       }
       .form-group{
        margin-bottom: 15px;
       }
       .form-group label{
        display: block;
        margin-bottom: 5px;
        color: #555;
        font-weight: 500;        
       }
       .form-group input, .form-group textarea{
        width: 100%;
        padding: 12px;
        border: 1px solid black;
        border-radius: 5px;
        font-size: 16px;
        transition: border 0.3s;
       }
        .form-group input:focus, .form-group textarea:focus{
            border-color:rgb(130, 174, 239);
            outline: none;
        }
        .submit-btn{
            width: 100%;
            padding: 12px;
            background: #a50c2a;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .submit-btn:hover{
            background:rgb(123, 115, 114);
        }
        .error{
            color: #d32f2f;
            font-size:12px;
            margin-top: 5px;
        }
        .success{
            color: #388e3c;
            font-size:14px;
            margin-bottom: 15px;
            text-align: center;
        }
        .info-box {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .info-box h3 {
            color: #1976d2;
            margin-bottom: 10px;
        }
        .info-box ul {
            margin-left: 20px;
            color: #333;
        }
        .info-box li {
            margin-bottom: 5px;
        }
        </style>
    </head>
    <body>
        <div class="registration-form">
            <h1>Become a Seller</h1>
            <div class="info-box">
                <h3>Why become a seller?</h3>
                <ul>
                    <li>Reach thousands of customers</li>
                    <li>Easy product management</li>
                    <li>Secure payment processing</li>
                    <li>Professional seller dashboard</li>
                </ul>
            </div>
            <?php if (isset($_SESSION['errors'])): ?>
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="success"><?php echo htmlspecialchars($_SESSION['success']); ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <form method="POST" action="seller_register_handler.php">
                <div class="form-group">
                    <label for="business_name">Business Name *</label>
                    <input type="text" id="business_name" name="business_name" placeholder="Enter your business name" required>
                </div>
                <div class="form-group">
                    <label for="business_description">Business Description *</label>
                    <textarea id="business_description" name="business_description" rows="4" placeholder="Describe your business and what you sell" required></textarea>
                </div>
                <div class="form-group">
                    <label for="contact_phone">Contact Phone *</label>
                    <input type="tel" id="contact_phone" name="contact_phone" placeholder="Enter your contact phone number" required>
                </div>
                <div class="form-group">
                    <label for="business_address">Business Address *</label>
                    <textarea id="business_address" name="business_address" rows="3" placeholder="Enter your business address" required></textarea>
                </div>
                <div class="form-group">
                    <label for="business_license">Business License Number</label>
                    <input type="text" id="business_license" name="business_license" placeholder="Enter your business license number (optional)">
                </div>
                <button type="submit" class="submit-btn">Submit Application</button>
            </form>
            <div style="text-align: center; margin-top: 15px;">
                <a href="landingpage.php" style="color: #a50c2a; text-decoration: none;">Back to Home</a>
            </div>
        </div>
    </body>
</html> 