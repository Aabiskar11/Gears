<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'];
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';

// Fetch featured products from database
$featuredProducts = [];
try {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          WHERE p.is_featured = 1 
                          LIMIT 4");
    $stmt->execute();
    $featuredProducts = $stmt->fetchAll();
} catch (PDOException $e) {
    // Handle error silently for now
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gears - Industrial Equipment Marketplace</title>
    <style type="text/css">
        * {
            text-decoration: none;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
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
        
        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1605152276897-4f618f831968?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            padding: 0 20px;
        }
        .hero-content h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }
        .hero-btn {
            background: crimson;
            color: white;
            padding: 12px 30px;
            border-radius: 30px;
            font-size: 1.1rem;
            transition: 0.3s;
        }
        .hero-btn:hover {
            background: #a50c2a;
        }
        
        /* Categories */
        .categories {
            padding: 50px 20px;
            text-align: center;
        }
        .section-title {
            font-size: 2.5rem;
            margin-bottom: 40px;
            color: #333;
        }
        .category-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .category-card {
            width: 250px;
            height: 150px;
            background: #f5f5f5;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
            font-weight: bold;
            font-size: 1.2rem;
            transition: 0.3s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-decoration: none;
            color: inherit;
        }
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
            background: crimson;
            color: white;
        }
        
        /* Featured Products */
        .featured-products {
            padding: 50px 20px;
            background: #f9f9f9;
        }
        .product-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .product-card {
            width: 250px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: 0.3s;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        .product-image {
            height: 200px;
            background: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .product-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }
        .product-info {
            padding: 20px;
        }
        .product-name {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: #333;
        }
        .product-price {
            font-size: 1.3rem;
            font-weight: bold;
            color: crimson;
            margin-bottom: 15px;
        }
        .product-card button {
            width: 100%;
            background: crimson;
            color: white;
            padding: 10px;
            border-radius: 5px;
        }
        .product-card button:hover {
            background: #a50c2a;
        }
        
        /* Newsletter */
        .newsletter {
            padding: 50px 20px;
            text-align: center;
            background: #333;
            color: white;
        }
        .newsletter-form {
            max-width: 500px;
            margin: 0 auto;
            display: flex;
        }
        .newsletter-form input {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 5px 0 0 5px;
            font-size: 1rem;
        }
        .newsletter-form button {
            padding: 15px 30px;
            background: crimson;
            color: white;
            border: none;
            border-radius: 0 5px 5px 0;
            cursor: pointer;
            font-weight: bold;
        }
        
        /* Footer */
        footer {
            background: #222;
            color: white;
            padding: 50px 20px;
        }
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
        }
        .footer-column h3 {
            font-size: 1.3rem;
            margin-bottom: 20px;
            color: crimson;
        }
        .footer-column ul li {
            margin-bottom: 10px;
        }
        .footer-column ul li a {
            color: #ddd;
            font-size: 1rem;
            transition: 0.3s;
        }
        .footer-column ul li a:hover {
            color: white;
        }
        .copyright {
            text-align: center;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #444;
            color: #aaa;
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
                <li><a href="#categories">Categories</a></li>
                <li><a href="#">About</a></li>
                <li><a href="#">Contact</a></li>
                <li><a href="seller_register.php">Become a Seller</a></li>
                <?php if ($isLoggedIn): ?>
                    <span class="user-info">Welcome, <?php echo htmlspecialchars($userName); ?>!</span>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <button><a href="admin/dashboard.php">Admin Panel</a></button>
                    <?php elseif (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'seller'): ?>
                        <button><a href="seller/dashboard.php">Seller Panel</a></button>
                    <?php endif; ?>
                    <button class="logout-btn"><a href="logout.php">Logout</a></button>
                <?php else: ?>
                    <button><a href="signin.php">Sign in</a></button>
                    <button><a href="login.php">Login</a></button>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Premium Industrial Equipment</h1>
            <p>Find the best machinery and tools for your business needs</p>
            <a href="product.php" class="hero-btn">Shop Now</a>
        </div>
    </section>

    <!-- Categories Section -->
    <section id="categories" class="categories">
        <h2 class="section-title">Shop By Category</h2>
        <div class="category-container">
            <a href="product.php?category=power-tools" class="category-card">Power Tools</a>
            <a href="product.php?category=heavy-machinery" class="category-card">Heavy Machinery</a>
            <a href="product.php?category=safety-equipment" class="category-card">Safety Equipment</a>
            <a href="product.php?category=construction-tools" class="category-card">Construction Tools</a>
            <a href="product.php?category=woodworking" class="category-card">Woodworking</a>
            <a href="product.php?category=metalworking" class="category-card">Metalworking</a>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured-products">
        <h2 class="section-title">Featured Products</h2>
        <div class="product-container">
            <?php if (!empty($featuredProducts)): ?>
                <?php foreach ($featuredProducts as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <div class="product-price">$<?php echo htmlspecialchars($product['price']); ?></div>
                            <button>Add to Cart</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback products if database is not available -->
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://via.placeholder.com/250x200" alt="Industrial Drill">
                    </div>
                    <div class="product-info">
                        <h3 class="product-name">Heavy Duty Industrial Drill</h3>
                        <div class="product-price">$249.99</div>
                        <button>Add to Cart</button>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://via.placeholder.com/250x200" alt="Circular Saw">
                    </div>
                    <div class="product-info">
                        <h3 class="product-name">Professional Circular Saw</h3>
                        <div class="product-price">$179.99</div>
                        <button>Add to Cart</button>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://via.placeholder.com/250x200" alt="Angle Grinder">
                    </div>
                    <div class="product-info">
                        <h3 class="product-name">Industrial Angle Grinder</h3>
                        <div class="product-price">$129.99</div>
                        <button>Add to Cart</button>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://via.placeholder.com/250x200" alt="Air Compressor">
                    </div>
                    <div class="product-info">
                        <h3 class="product-name">Heavy Duty Air Compressor</h3>
                        <div class="product-price">$499.99</div>
                        <button>Add to Cart</button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="newsletter">
        <h2 class="section-title">Stay Updated</h2>
        <p>Subscribe to our newsletter for the latest products and deals</p>
        <form class="newsletter-form">
            <input type="email" placeholder="Enter your email address">
            <button type="submit">Subscribe</button>
        </form>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-column">
                <h3>Gears</h3>
                <p>Your one-stop shop for all industrial equipment needs.</p>
            </div>
            <div class="footer-column">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="landingpage.php">Home</a></li>
                    <li><a href="product.php">Shop</a></li>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Customer Service</h3>
                <ul>
                    <li><a href="#">FAQs</a></li>
                    <li><a href="#">Shipping Policy</a></li>
                    <li><a href="#">Returns & Warranty</a></li>
                    <li><a href="#">Track Order</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Contact Us</h3>
                <ul>
                    <li>123 Industrial Park, City</li>
                    <li>Phone: (123) 456-7890</li>
                    <li>Email: info@gears.com</li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2023 Gears. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>