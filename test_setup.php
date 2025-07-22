<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Database Connection and Setup</h2>";

try {
    require_once 'config/database.php';
    echo "<p>✓ Database connection successful</p>";
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✓ Users table exists</p>";
    } else {
        echo "<p style='color: red;'>✗ Users table does not exist</p>";
        exit;
    }
    
    // Check if role column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✓ Role column exists</p>";
    } else {
        echo "<p>Adding role column...</p>";
        $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('customer', 'seller', 'admin') DEFAULT 'customer' AFTER password");
        echo "<p>✓ Role column added</p>";
    }
    
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT id, email, role FROM users WHERE email = ?");
    $stmt->execute(['admin@gears.com']);
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<p>✓ Admin user exists (ID: {$admin['id']}, Role: {$admin['role']})</p>";
        
        // Update admin role if needed
        if ($admin['role'] !== 'admin') {
            $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE email = ?");
            $stmt->execute(['admin@gears.com']);
            echo "<p>✓ Admin role updated</p>";
        }
    } else {
        echo "<p>Creating admin user...</p>";
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Admin User', 'admin@gears.com', $adminPassword, 'admin']);
        echo "<p>✓ Admin user created</p>";
    }
    
    // Check if seller users exist
    $sellerEmails = ['john@tools.com', 'sarah@equipment.com'];
    foreach ($sellerEmails as $email) {
        $stmt = $pdo->prepare("SELECT id, email, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $seller = $stmt->fetch();
        
        if ($seller) {
            echo "<p>✓ Seller user exists: {$email} (Role: {$seller['role']})</p>";
        } else {
            echo "<p>Creating seller user: {$email}</p>";
            $sellerPassword = password_hash('seller123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([ucfirst(explode('@', $email)[0]), $email, $sellerPassword, 'seller']);
            echo "<p>✓ Seller user created: {$email}</p>";
        }
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
    echo "<p style='color: red;'>Database Error: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?> 