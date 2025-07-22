# Cart System Setup Guide

## Problem
The original SQL was failing because of foreign key constraint issues. The tables were being created with foreign key constraints before the referenced tables existed.

## Solution
I've created a fixed version (`database/cart_system_fixed.sql`) that:

1. **Creates tables in the correct order** (no foreign key dependencies first)
2. **Adds foreign key constraints separately** (after all tables exist)
3. **Uses `IF NOT EXISTS`** to prevent errors if tables already exist
4. **Uses `INSERT IGNORE`** to prevent duplicate data errors

## Step-by-Step Setup

### Method 1: Using the Fixed SQL File (Recommended)

1. **Open phpMyAdmin**
   - Go to http://localhost/phpmyadmin
   - Select your `gears_db` database

2. **Import the Fixed SQL**
   - Click "Import" tab
   - Choose file: `database/cart_system_fixed.sql`
   - Click "Go"

3. **Verify Setup**
   - Check that all tables were created
   - Look for any error messages

### Method 2: Using the Setup Script

1. **Run the Setup Script**
   - Open your browser
   - Go to: `http://localhost/Gears/setup_cart_system.php`
   - This will automatically run the fixed SQL

2. **Check Results**
   - The script will show you what was created
   - Look for any error messages

### Method 3: Manual Step-by-Step (if you prefer)

If you want to run the SQL manually, follow this order:

```sql
-- Step 1: Create cart table (no foreign keys)
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_product (user_id, product_id),
    INDEX idx_user_id (user_id),
    INDEX idx_product_id (product_id)
);

-- Step 2: Create user_addresses table
CREATE TABLE IF NOT EXISTS user_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    address_type ENUM('shipping', 'billing', 'both') DEFAULT 'both',
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    company VARCHAR(100),
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL DEFAULT 'United States',
    phone VARCHAR(20),
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_address_type (address_type)
);

-- Step 3: Create orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_address TEXT NOT NULL,
    billing_address TEXT NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_order_number (order_number),
    INDEX idx_status (status),
    INDEX idx_payment_status (payment_status)
);

-- Step 4: Create order_items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    product_price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id)
);

-- Step 5: Create payment_transactions table
CREATE TABLE IF NOT EXISTS payment_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    transaction_id VARCHAR(100) UNIQUE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    payment_method VARCHAR(50) NOT NULL,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    gateway_response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_order_id (order_id),
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_status (status)
);

-- Step 6: Add foreign key constraints
ALTER TABLE cart 
ADD CONSTRAINT fk_cart_user 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE cart 
ADD CONSTRAINT fk_cart_product 
FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE;

ALTER TABLE user_addresses 
ADD CONSTRAINT fk_addresses_user 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE orders 
ADD CONSTRAINT fk_orders_user 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE order_items 
ADD CONSTRAINT fk_order_items_order 
FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE;

ALTER TABLE order_items 
ADD CONSTRAINT fk_order_items_product 
FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL;

ALTER TABLE payment_transactions 
ADD CONSTRAINT fk_payment_transactions_order 
FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE;
```

## Verification

After running the setup, verify everything worked by running these queries:

```sql
-- Check if all tables exist
SHOW TABLES LIKE 'cart';
SHOW TABLES LIKE 'orders';
SHOW TABLES LIKE 'order_items';
SHOW TABLES LIKE 'payment_transactions';
SHOW TABLES LIKE 'user_addresses';

-- Check foreign key constraints
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = 'gears_db' 
AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME, CONSTRAINT_NAME;
```

## Common Errors and Solutions

### Error: "Cannot add foreign key constraint"
**Cause**: The referenced table doesn't exist or the data types don't match.

**Solution**: 
1. Make sure you've imported the main database first (`complete_database.sql`)
2. Run the tables in the correct order (use the fixed SQL file)

### Error: "Table already exists"
**Cause**: Tables were already created from a previous attempt.

**Solution**: 
1. Use the fixed SQL file (it has `IF NOT EXISTS`)
2. Or drop the tables first:
```sql
DROP TABLE IF EXISTS payment_transactions;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS user_addresses;
DROP TABLE IF EXISTS cart;
```

### Error: "Duplicate entry for key"
**Cause**: Sample data already exists.

**Solution**: 
1. The fixed SQL uses `INSERT IGNORE` to prevent this
2. Or manually delete existing data first

## Testing the Cart System

Once setup is complete, test the cart functionality:

1. **Login** to your account
2. **Go to products page** (`product.php`)
3. **Click "Add to Cart"** on any product
4. **Check the cart icon** in navigation (should show count)
5. **Click cart icon** to view cart
6. **Test checkout process**

## Need Help?

If you're still having issues:

1. **Check XAMPP logs** for MySQL errors
2. **Verify database connection** in `config/database.php`
3. **Make sure all files exist** in the correct locations
4. **Try the setup script** (`setup_cart_system.php`) for automatic setup

The fixed SQL file should resolve the foreign key constraint issues you were experiencing! 