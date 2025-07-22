<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'];
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';

// Get cart count for logged in users
$cartCount = 0;
if ($isLoggedIn) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(c.id) as cart_count
            FROM cart c
            WHERE c.user_id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $cartCount = $stmt->fetch()['cart_count'];
    } catch (PDOException $e) {
        // Handle error silently
    }
}

// Get category from URL parameter
$category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Fetch products based on category
$products = [];
$categoryName = 'All Products';
$resultCount = 0;

try {
    if ($category !== 'all') {
        // Get category ID from slug
        $stmt = $pdo->prepare("SELECT id, name FROM categories WHERE slug = ?");
        $stmt->execute([$category]);
        $categoryData = $stmt->fetch();
        
        if ($categoryData) {
            $categoryId = $categoryData['id'];
            $categoryName = $categoryData['name'];
            
            // Fetch products for specific category
            $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                                  LEFT JOIN categories c ON p.category_id = c.id 
                                  WHERE p.category_id = ? 
                                  ORDER BY p.created_at DESC");
            $stmt->execute([$categoryId]);
            $products = $stmt->fetchAll();
        }
    } else {
        // Fetch all products
        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                              LEFT JOIN categories c ON p.category_id = c.id 
                              ORDER BY p.created_at DESC");
        $stmt->execute();
        $products = $stmt->fetchAll();
    }
    
    $resultCount = count($products);
} catch (PDOException $e) {
    // Handle error silently for now
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($categoryName); ?> - Gears</title>
    <style type="text/css">
        * {
            text-decoration: none;
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
        
        /* Cart Icon Styles */
        .cart-icon {
            position: relative;
            display: inline-block;
            margin-right: 20px;
        }
        .cart-icon a {
            color: white;
            font-size: 20px;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }
        
        /* Product Page Specific Styles */
        .breadcrumb {
            padding: 20px;
            background: #f5f5f5;
            margin-bottom: 30px;
            font-size: 0.9rem;
        }
        .breadcrumb a {
            color: crimson;
            transition: 0.3s;
        }
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        .page-title {
            text-align: center;
            margin-bottom: 40px;
            font-size: 2.5rem;
            color: #333;
        }
        .filter-section {
            display: flex;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto 30px;
            padding: 0 20px;
            align-items: center;
        }
        .filter-options select {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
        }
        .result-count {
            color: #666;
        }
        
        /* Product Grid */
        .product-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
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
            min-height: 50px;
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
            transition: 0.3s;
        }
        .product-card button:hover {
            background: #a50c2a;
        }
        .product-card button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        /* Pagination */
        .pagination {
            text-align: center;
            margin: 40px 0;
        }
        .pagination a {
            display: inline-block;
            padding: 10px 15px;
            margin: 0 5px;
            border-radius: 5px;
            transition: 0.3s;
        }
        .pagination a:first-child {
            background: crimson;
            color: white;
        }
        .pagination a:not(:first-child) {
            background: #f5f5f5;
            color: #333;
        }
        .pagination a:hover {
            background: #ddd;
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
            display: block;
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
                <li><a href="landingpage.php#categories">Categories</a></li>
                <li><a href="#">About</a></li>
                <li><a href="#">Contact</a></li>
                <?php if ($isLoggedIn): ?>
                    <div class="cart-icon">
                        <a href="cart.php">
                            🛒
                            <?php if ($cartCount > 0): ?>
                                <span class="cart-count"><?php echo $cartCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                    <span class="user-info">Welcome, <?php echo htmlspecialchars($userName); ?>!</span>
                    <button class="logout-btn"><a href="logout.php">Logout</a></button>
                <?php else: ?>
                    <button><a href="signin.php">Sign in</a></button>
                    <button><a href="login.php">Login</a></button>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb">
        <a href="index.php">Home</a> > 
        <a href="product.php">Shop</a> > 
        <?php echo htmlspecialchars($categoryName); ?>
    </div>

    <!-- Page Title -->
    <h1 class="page-title">
        <?php echo htmlspecialchars($categoryName); ?> Products
    </h1>

    <!-- Filter Section -->
    <div class="filter-section">
        <div class="filter-options">
            <select id="sort-by">
                <option value="popular">Sort by: Popular</option>
                <option value="price-low">Price: Low to High</option>
                <option value="price-high">Price: High to Low</option>
                <option value="newest">Newest Arrivals</option>
            </select>
        </div>
        <div class="result-count">
            Showing <?php echo $resultCount; ?> products
        </div>
    </div>

    <!-- Product Grid -->
    <section class="featured-products">
        <div class="product-container">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <div class="product-price">$<?php echo htmlspecialchars($product['price']); ?></div>
                            <?php if ($isLoggedIn): ?>
                                <button onclick="addToCart(<?php echo $product['id']; ?>)" class="add-to-cart-btn">
                                    Add to Cart
                                </button>
                            <?php else: ?>
                                <button onclick="loginRequired()" class="add-to-cart-btn">
                                    Add to Cart
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback products if database is not available or no products found -->
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://via.placeholder.com/250x200" alt="Industrial Drill">
                    </div>
                    <div class="product-info">
                        <h3 class="product-name">Heavy Duty Industrial Drill</h3>
                        <div class="product-price">$249.99</div>
                        <?php if ($isLoggedIn): ?>
                            <button onclick="addToCart(1)" class="add-to-cart-btn">Add to Cart</button>
                        <?php else: ?>
                            <button onclick="loginRequired()" class="add-to-cart-btn">Add to Cart</button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://via.placeholder.com/250x200" alt="Circular Saw">
                    </div>
                    <div class="product-info">
                        <h3 class="product-name">Professional Circular Saw</h3>
                        <div class="product-price">$179.99</div>
                        <?php if ($isLoggedIn): ?>
                            <button onclick="addToCart(2)" class="add-to-cart-btn">Add to Cart</button>
                        <?php else: ?>
                            <button onclick="loginRequired()" class="add-to-cart-btn">Add to Cart</button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://via.placeholder.com/250x200" alt="Angle Grinder">
                    </div>
                    <div class="product-info">
                        <h3 class="product-name">Industrial Angle Grinder</h3>
                        <div class="product-price">$129.99</div>
                        <?php if ($isLoggedIn): ?>
                            <button onclick="addToCart(3)" class="add-to-cart-btn">Add to Cart</button>
                        <?php else: ?>
                            <button onclick="loginRequired()" class="add-to-cart-btn">Add to Cart</button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://via.placeholder.com/250x200" alt="Air Compressor">
                    </div>
                    <div class="product-info">
                        <h3 class="product-name">Heavy Duty Air Compressor</h3>
                        <div class="product-price">$499.99</div>
                        <?php if ($isLoggedIn): ?>
                            <button onclick="addToCart(4)" class="add-to-cart-btn">Add to Cart</button>
                        <?php else: ?>
                            <button onclick="loginRequired()" class="add-to-cart-btn">Add to Cart</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Pagination -->
    <div class="pagination">
        <a href="#">1</a>
        <a href="#">2</a>
        <a href="#">3</a>
        <a href="#">Next</a>
    </div>

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

    <script>
        function addToCart(productId) {
            const button = event.target;
            const originalText = button.textContent;
            
            // Disable button and show loading
            button.disabled = true;
            button.textContent = 'Adding...';
            
            fetch('cart_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add&product_id=${productId}&quantity=1`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    button.textContent = 'Added!';
                    button.style.background = '#4CAF50';
                    
                    // Update cart count in navigation
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) {
                        const currentCount = parseInt(cartCount.textContent) || 0;
                        cartCount.textContent = currentCount + 1;
                    } else {
                        // Create cart count if it doesn't exist
                        const cartIcon = document.querySelector('.cart-icon a');
                        const newCartCount = document.createElement('span');
                        newCartCount.className = 'cart-count';
                        newCartCount.textContent = '1';
                        cartIcon.appendChild(newCartCount);
                    }
                    
                    // Reset button after 2 seconds
                    setTimeout(() => {
                        button.textContent = originalText;
                        button.style.background = 'crimson';
                        button.disabled = false;
                    }, 2000);
                } else {
                    alert(data.error || 'Error adding item to cart');
                    button.textContent = originalText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding item to cart');
                button.textContent = originalText;
                button.disabled = false;
            });
        }

        function loginRequired() {
            alert('Please log in to add items to your cart.');
            window.location.href = 'login.php';
        }
    </script>
</body>
</html>