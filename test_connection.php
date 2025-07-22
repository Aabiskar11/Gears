<?php
// Test database connection and show data
require_once 'config/database.php';

echo "<h2>🔍 Database Connection Test</h2>";

try {
    // Test connection
    echo "<p>✅ Database connection successful!</p>";
    
    // Show database info
    echo "<h3>Database Information:</h3>";
    echo "<p><strong>Database:</strong> gears_db</p>";
    echo "<p><strong>Host:</strong> localhost</p>";
    
    // Count records in each table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch()['count'];
    echo "<p><strong>Users:</strong> $userCount records</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
    $categoryCount = $stmt->fetch()['count'];
    echo "<p><strong>Categories:</strong> $categoryCount records</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    $productCount = $stmt->fetch()['count'];
    echo "<p><strong>Products:</strong> $productCount records</p>";
    
    // Show sample data
    echo "<h3>Sample Categories:</h3>";
    $stmt = $pdo->query("SELECT * FROM categories LIMIT 3");
    $categories = $stmt->fetchAll();
    echo "<ul>";
    foreach ($categories as $category) {
        echo "<li>{$category['name']} ({$category['slug']})</li>";
    }
    echo "</ul>";
    
    echo "<h3>Sample Products:</h3>";
    $stmt = $pdo->query("SELECT p.name, p.price, c.name as category FROM products p LEFT JOIN categories c ON p.category_id = c.id LIMIT 5");
    $products = $stmt->fetchAll();
    echo "<ul>";
    foreach ($products as $product) {
        echo "<li>{$product['name']} - \${$product['price']} ({$product['category']})</li>";
    }
    echo "</ul>";
    
    echo "<h3>✅ All systems working correctly!</h3>";
    echo "<p><a href='index.php' style='background: crimson; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Website</a></p>";
    
} catch (PDOException $e) {
    echo "<h3>❌ Connection Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Please run <a href='setup_database.php'>setup_database.php</a> first.</p>";
}
?> 