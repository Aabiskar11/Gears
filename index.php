<?php
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'];
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Gears - Industrial Equipment Marketplace</title>
        <style type="text/css">
            *{
                text-decoration: none;
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: Arial, sans-serif;
            }
            .Navbar{
                background:crimson;
                font-family: calibri;
                padding-right: 15px;
                padding-left: 15px;
                position: sticky;
                top: 0;
                z-index: 100;
            }
            .Navdiv{
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 10px 0;
            }
            .logo a{
                font-size: 35px;
                font-weight: 600;
                color: white;
            }        
            li{
                list-style: none;
                display: inline-block;
            }
            li a{
                color: white;
                font-size: 20px;
                font-weight: bold;
                margin-right: 25px;
                transition: 0.3s;
            }
            li a:hover {
                color: #ddd;
            }
            button{
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
            button a{
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
            .hero-section {
                background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1605152276897-4f618f831968?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
                background-size: cover;
                background-position: center;
                height: 400px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                text-align: center;
                padding: 0 20px;
            }
            .hero-content h1 {
                font-size: 2.5rem;
                margin-bottom: 20px;
            }
            .hero-content p {
                font-size: 1.1rem;
                margin-bottom: 30px;
            }
            .cta-btn {
                background: crimson;
                color: white;
                padding: 12px 30px;
                border-radius: 30px;
                font-size: 1.1rem;
                text-decoration: none;
                transition: 0.3s;
                display: inline-block;
            }
            .cta-btn:hover {
                background: #a50c2a;
            }
        </style>
    </head>
    <body>
        <nav class="Navbar">
            <div class="Navdiv">
                <div class="logo"><a href="index.php">Gears</a></div>
                <ul>
                    <li><a href="landingpage.php">Home</a></li>
                    <li><a href="product.php">Shop</a></li>
                    <li><a href="landingpage.php#categories">Categories</a></li>
                    <li><a href="#">About</a></li>
                    <li><a href="#">Contact</a></li>
                    <?php if ($isLoggedIn): ?>
                        <span class="user-info">Welcome, <?php echo htmlspecialchars($userName); ?>!</span>
                        <button class="logout-btn"><a href="logout.php">Logout</a></button>
                    <?php else: ?>
                        <button><a href="signin.php">Sign in</a></button>
                        <button><a href="login.php">Login</a></button>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
        
        <section class="hero-section">
            <div class="hero-content">
                <h1>Welcome to Gears</h1>
                <p>Your one-stop destination for premium industrial equipment and machinery</p>
                <a href="landingpage.php" class="cta-btn">Explore Products</a>
            </div>
        </section>
        
        <div style="text-align: center; padding: 50px 20px;">
            <h1><i>This is the website where you can buy any machinery item of any sector</i></h1>
        </div>
    </body>
</html>

