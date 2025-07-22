<?php
require_once 'config/database.php';

echo "<h2>Fixing Admin Password</h2>";

try {
    // Generate the correct password hash for 'admin123'
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Update the admin user's password
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->execute([$adminPassword, 'admin@gears.com']);
    
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✓ Admin password updated successfully!</p>";
    } else {
        echo "<p style='color: orange;'>No changes made (admin user might not exist)</p>";
    }
    
    // Also update seller passwords
    $sellerPassword = password_hash('seller123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->execute([$sellerPassword, 'john@tools.com']);
    echo "<p>✓ John's password updated</p>";
    
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->execute([$sellerPassword, 'sarah@equipment.com']);
    echo "<p>✓ Sarah's password updated</p>";
    
    echo "<h3>Updated Login Credentials:</h3>";
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