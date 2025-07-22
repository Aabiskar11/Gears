<?php
/**
 * Cart System Setup Script
 * 
 * This script sets up the cart and payment system database tables
 * Run this script after setting up the main database
 */

require_once 'config/database.php';

echo "<h1>Gears Cart System Setup</h1>";
echo "<p>Setting up cart and payment system database tables...</p>";

try {
    // Read the cart system SQL file
    $sql_file = 'database/cart_system.sql';
    
    if (!file_exists($sql_file)) {
        throw new Exception("SQL file not found: $sql_file");
    }
    
    $sql_content = file_get_contents($sql_file);
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql_content)));
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue; // Skip comments and empty lines
        }
        
        try {
            $pdo->exec($statement);
            $success_count++;
            echo "<p style='color: green;'>✓ Executed: " . substr($statement, 0, 50) . "...</p>";
        } catch (PDOException $e) {
            $error_count++;
            echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
            echo "<p style='color: gray;'>Statement: " . substr($statement, 0, 100) . "...</p>";
        }
    }
    
    echo "<h2>Setup Complete!</h2>";
    echo "<p>Successfully executed: $success_count statements</p>";
    echo "<p>Errors: $error_count</p>";
    
    if ($error_count == 0) {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>🎉 Cart System Setup Successful!</h3>";
        echo "<p>The following tables have been created:</p>";
        echo "<ul>";
        echo "<li>cart - Shopping cart items</li>";
        echo "<li>orders - Customer orders</li>";
        echo "<li>order_items - Order line items</li>";
        echo "<li>payment_transactions - Payment records</li>";
        echo "<li>user_addresses - Customer addresses</li>";
        echo "</ul>";
        echo "<p>You can now use the cart and checkout functionality!</p>";
        echo "</div>";
        
        // Test the cart functionality
        echo "<h3>Testing Cart Functionality</h3>";
        testCartFunctionality();
        
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>⚠️ Setup Completed with Errors</h3>";
        echo "<p>Some database operations failed. Please check the errors above and try again.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>❌ Setup Failed</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}

function testCartFunctionality() {
    global $pdo;
    
    try {
        // Test if tables exist
        $tables = ['cart', 'orders', 'order_items', 'payment_transactions', 'user_addresses'];
        $existing_tables = [];
        
        foreach ($tables as $table) {
            $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            if ($stmt->rowCount() > 0) {
                $existing_tables[] = $table;
            }
        }
        
        echo "<p>✅ Tables created: " . implode(', ', $existing_tables) . "</p>";
        
        // Test stored procedures
        $procedures = ['AddToCart', 'GenerateOrderNumber', 'CreateOrderFromCart'];
        $existing_procedures = [];
        
        foreach ($procedures as $procedure) {
            $stmt = $pdo->prepare("SHOW PROCEDURE STATUS WHERE Name = ?");
            $stmt->execute([$procedure]);
            if ($stmt->rowCount() > 0) {
                $existing_procedures[] = $procedure;
            }
        }
        
        echo "<p>✅ Stored procedures created: " . implode(', ', $existing_procedures) . "</p>";
        
        // Test views
        $views = ['user_cart_summary', 'order_summary'];
        $existing_views = [];
        
        foreach ($views as $view) {
            $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$view]);
            if ($stmt->rowCount() > 0) {
                $existing_views[] = $view;
            }
        }
        
        echo "<p>✅ Views created: " . implode(', ', $existing_views) . "</p>";
        
        echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>🚀 Ready to Use!</h4>";
        echo "<p>Your cart system is now ready. You can:</p>";
        echo "<ul>";
        echo "<li>Add items to cart from the product page</li>";
        echo "<li>View and manage cart items</li>";
        echo "<li>Proceed through checkout</li>";
        echo "<li>Process payments (simulated)</li>";
        echo "</ul>";
        echo "<p><strong>Next steps:</strong></p>";
        echo "<ol>";
        echo "<li>Test the cart functionality by logging in and adding items</li>";
        echo "<li>Go through the checkout process</li>";
        echo "<li>Integrate real payment gateways using the PAYMENT_INTEGRATION.md guide</li>";
        echo "</ol>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Testing failed: " . $e->getMessage() . "</p>";
    }
}

// Add some basic styling
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    h1 { color: #333; border-bottom: 2px solid crimson; padding-bottom: 10px; }
    h2 { color: #666; }
    h3 { color: #333; }
    p { line-height: 1.6; }
    ul, ol { line-height: 1.8; }
    .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; }
    .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; }
    .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; }
</style>";
?> 