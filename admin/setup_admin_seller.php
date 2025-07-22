<?php
require_once 'config/database.php';

echo "<h2>Setting up Admin and Seller Users</h2>";

try {
    // First, let's add the role column if it doesn't exist
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS role ENUM('customer', 'seller', 'admin') DEFAULT 'customer' AFTER password");
    echo "<p>✓ Role column added/verified</p>";
    
    // Add seller_id column to products if it doesn't exist
    $pdo->exec("ALTER TABLE products ADD COLUMN IF NOT EXISTS seller_id INT AFTER category_id");
    echo "<p>✓ Seller ID column added to products</p>";
    
    // Create seller_profiles table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS seller_profiles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        business_name VARCHAR(200) NOT NULL,
        business_description TEXT,
        contact_phone VARCHAR(20),
        business_address TEXT,
        business_license VARCHAR(100),
        is_approved BOOLEAN DEFAULT FALSE,
        approved_by INT,
        approved_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user_id (user_id),
        INDEX idx_approved (is_approved)
    )");
    echo "<p>✓ Seller profiles table created</p>";
    
    // Create admin_actions table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_actions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        action_type ENUM('approve_seller', 'reject_seller', 'suspend_seller', 'delete_product', 'feature_product') NOT NULL,
        target_id INT NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_admin_id (admin_id),
        INDEX idx_action_type (action_type),
        INDEX idx_created_at (created_at)
    )");
    echo "<p>✓ Admin actions table created</p>";
    
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['admin@gears.com']);
    $admin = $stmt->fetch();
    
    if (!$admin) {
        // Create admin user (password: admin123)
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Admin User', 'admin@gears.com', $adminPassword, 'admin']);
        echo "<p>✓ Admin user created</p>";
    } else {
        // Update existing admin user role
        $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE email = ?");
        $stmt->execute(['admin@gears.com']);
        echo "<p>✓ Admin user role updated</p>";
    }
    
    // Check if seller users exist
    $sellerEmails = ['john@tools.com', 'sarah@equipment.com'];
    $sellerData = [
        ['John Tools', 'john@tools.com', 'John\'s Tool Shop', 'Professional tools and equipment for construction and industrial use', '+1234567890', '123 Tool Street, Industrial District'],
        ['Sarah Equipment', 'sarah@equipment.com', 'Sarah\'s Equipment Co', 'Heavy machinery and industrial equipment supplier', '+0987654321', '456 Equipment Avenue, Business Park']
    ];
    
    foreach ($sellerData as $index => $seller) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$seller[1]]);
        $existingSeller = $stmt->fetch();
        
        if (!$existingSeller) {
            // Create seller user (password: seller123)
            $sellerPassword = password_hash('seller123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$seller[0], $seller[1], $sellerPassword, 'seller']);
            $sellerId = $pdo->lastInsertId();
            echo "<p>✓ Seller user created: {$seller[0]}</p>";
            
            // Create seller profile
            $stmt = $pdo->prepare("INSERT INTO seller_profiles (user_id, business_name, business_description, contact_phone, business_address, is_approved, approved_by) VALUES (?, ?, ?, ?, ?, TRUE, ?)");
            $stmt->execute([$sellerId, $seller[2], $seller[3], $seller[4], $seller[5], $admin['id'] ?? 1]);
            echo "<p>✓ Seller profile created for: {$seller[0]}</p>";
        } else {
            // Update existing seller role
            $stmt = $pdo->prepare("UPDATE users SET role = 'seller' WHERE email = ?");
            $stmt->execute([$seller[1]]);
            echo "<p>✓ Seller user role updated: {$seller[0]}</p>";
        }
    }
    
    // Update existing products to assign them to sellers
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = 'john@tools.com'");
    $stmt->execute();
    $johnId = $stmt->fetch()['id'];
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = 'sarah@equipment.com'");
    $stmt->execute();
    $sarahId = $stmt->fetch()['id'];
    
    if ($johnId) {
        $pdo->exec("UPDATE products SET seller_id = $johnId WHERE category_id IN (1, 4, 5)");
        echo "<p>✓ Products assigned to John's Tool Shop</p>";
    }
    
    if ($sarahId) {
        $pdo->exec("UPDATE products SET seller_id = $sarahId WHERE category_id IN (2, 3, 6)");
        echo "<p>✓ Products assigned to Sarah's Equipment Co</p>";
    }
    
    echo "<h3>Login Credentials:</h3>";
    echo "<p><strong>Admin:</strong><br>";
    echo "Email: admin@gears.com<br>";
    echo "Password: admin123</p>";
    
    echo "<p><strong>Sellers:</strong><br>";
    echo "Email: john@tools.com<br>";
    echo "Password: seller123<br><br>";
    echo "Email: sarah@equipment.com<br>";
    echo "Password: seller123</p>";
    
    echo "<p><a href='login.php'>Go to Login Page</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?> 