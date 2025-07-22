-- =====================================================
-- GEARS CART SYSTEM - FIXED DATABASE SQL
-- Industrial Equipment Marketplace Cart & Payment System
-- =====================================================

USE gears_db;

-- =====================================================
-- CART SYSTEM TABLES (IN CORRECT ORDER)
-- =====================================================

-- 1. Shopping cart table (no foreign key dependencies)
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

-- 2. User addresses table (depends on users table)
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

-- 3. Orders table (depends on users table)
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

-- 4. Order items table (depends on orders and products tables)
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

-- 5. Payment transactions table (depends on orders table)
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

-- =====================================================
-- ADD FOREIGN KEY CONSTRAINTS (AFTER ALL TABLES EXIST)
-- =====================================================

-- Add foreign key constraints for cart table
ALTER TABLE cart 
ADD CONSTRAINT fk_cart_user 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE cart 
ADD CONSTRAINT fk_cart_product 
FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE;

-- Add foreign key constraint for user_addresses table
ALTER TABLE user_addresses 
ADD CONSTRAINT fk_addresses_user 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Add foreign key constraint for orders table
ALTER TABLE orders 
ADD CONSTRAINT fk_orders_user 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Add foreign key constraints for order_items table
ALTER TABLE order_items 
ADD CONSTRAINT fk_order_items_order 
FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE;

ALTER TABLE order_items 
ADD CONSTRAINT fk_order_items_product 
FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL;

-- Add foreign key constraint for payment_transactions table
ALTER TABLE payment_transactions 
ADD CONSTRAINT fk_payment_transactions_order 
FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE;

-- =====================================================
-- SAMPLE DATA FOR TESTING
-- =====================================================

-- Sample user addresses (assuming user_id = 1 exists)
INSERT IGNORE INTO user_addresses (user_id, address_type, first_name, last_name, company, address_line1, city, state, postal_code, country, phone, is_default) VALUES
(1, 'both', 'John', 'Doe', 'Industrial Solutions Inc.', '123 Main Street', 'New York', 'NY', '10001', 'United States', '+1-555-123-4567', TRUE),
(1, 'shipping', 'John', 'Doe', 'Industrial Solutions Inc.', '456 Warehouse Ave', 'Los Angeles', 'CA', '90210', 'United States', '+1-555-987-6543', FALSE);

-- =====================================================
-- STORED PROCEDURES FOR COMMON OPERATIONS
-- =====================================================

DELIMITER //

-- Procedure to add item to cart
CREATE PROCEDURE IF NOT EXISTS AddToCart(
    IN p_user_id INT,
    IN p_product_id INT,
    IN p_quantity INT
)
BEGIN
    DECLARE existing_quantity INT DEFAULT 0;
    
    -- Check if item already exists in cart
    SELECT quantity INTO existing_quantity 
    FROM cart 
    WHERE user_id = p_user_id AND product_id = p_product_id;
    
    IF existing_quantity > 0 THEN
        -- Update existing item
        UPDATE cart 
        SET quantity = existing_quantity + p_quantity,
            updated_at = CURRENT_TIMESTAMP
        WHERE user_id = p_user_id AND product_id = p_product_id;
    ELSE
        -- Add new item
        INSERT INTO cart (user_id, product_id, quantity)
        VALUES (p_user_id, p_product_id, p_quantity);
    END IF;
END //

-- Procedure to generate order number
CREATE PROCEDURE IF NOT EXISTS GenerateOrderNumber(OUT order_number VARCHAR(50))
BEGIN
    DECLARE timestamp_part VARCHAR(20);
    DECLARE random_part VARCHAR(10);
    
    SET timestamp_part = DATE_FORMAT(NOW(), '%Y%m%d%H%i%s');
    SET random_part = LPAD(FLOOR(RAND() * 10000), 4, '0');
    SET order_number = CONCAT('ORD-', timestamp_part, '-', random_part);
END //

-- Procedure to create order from cart
CREATE PROCEDURE IF NOT EXISTS CreateOrderFromCart(
    IN p_user_id INT,
    IN p_shipping_address TEXT,
    IN p_billing_address TEXT,
    IN p_payment_method VARCHAR(50),
    OUT p_order_id INT
)
BEGIN
    DECLARE cart_total DECIMAL(10,2) DEFAULT 0;
    DECLARE new_order_number VARCHAR(50);
    DECLARE done INT DEFAULT FALSE;
    DECLARE cart_item_id INT;
    DECLARE cart_product_id INT;
    DECLARE cart_quantity INT;
    DECLARE product_price DECIMAL(10,2);
    DECLARE product_name VARCHAR(200);
    
    DECLARE cart_cursor CURSOR FOR
        SELECT c.id, c.product_id, c.quantity, p.price, p.name
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = p_user_id;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Calculate cart total
    SELECT COALESCE(SUM(c.quantity * p.price), 0) INTO cart_total
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = p_user_id;
    
    -- Generate order number
    CALL GenerateOrderNumber(new_order_number);
    
    -- Create order
    INSERT INTO orders (user_id, order_number, total_amount, shipping_address, billing_address, payment_method)
    VALUES (p_user_id, new_order_number, cart_total, p_shipping_address, p_billing_address, p_payment_method);
    
    SET p_order_id = LAST_INSERT_ID();
    
    -- Add order items
    OPEN cart_cursor;
    read_loop: LOOP
        FETCH cart_cursor INTO cart_item_id, cart_product_id, cart_quantity, product_price, product_name;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, subtotal)
        VALUES (p_order_id, cart_product_id, product_name, product_price, cart_quantity, (cart_quantity * product_price));
    END LOOP;
    CLOSE cart_cursor;
    
    -- Clear cart
    DELETE FROM cart WHERE user_id = p_user_id;
END //

DELIMITER ;

-- =====================================================
-- TRIGGERS FOR AUTOMATIC UPDATES
-- =====================================================

DELIMITER //

-- Trigger to update product stock when order is created
CREATE TRIGGER IF NOT EXISTS after_order_item_insert
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    UPDATE products 
    SET stock_quantity = stock_quantity - NEW.quantity
    WHERE id = NEW.product_id;
END //

DELIMITER ;

-- =====================================================
-- VIEWS FOR COMMON QUERIES
-- =====================================================

-- View for user cart summary
CREATE OR REPLACE VIEW user_cart_summary AS
SELECT 
    c.user_id,
    COUNT(c.id) as item_count,
    SUM(c.quantity) as total_quantity,
    SUM(c.quantity * p.price) as total_amount
FROM cart c
JOIN products p ON c.product_id = p.id
GROUP BY c.user_id;

-- View for order summary
CREATE OR REPLACE VIEW order_summary AS
SELECT 
    o.id,
    o.order_number,
    o.user_id,
    u.fullname as customer_name,
    o.total_amount,
    o.status,
    o.payment_status,
    o.created_at,
    COUNT(oi.id) as item_count
FROM orders o
JOIN users u ON o.user_id = u.id
LEFT JOIN order_items oi ON o.id = oi.order_id
GROUP BY o.id, o.order_number, o.user_id, u.fullname, o.total_amount, o.status, o.payment_status, o.created_at;

-- =====================================================
-- USEFUL QUERIES FOR TESTING
-- =====================================================

-- Test query to check if tables were created successfully
SELECT 
    TABLE_NAME,
    TABLE_ROWS,
    CREATE_TIME
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'gears_db' 
AND TABLE_NAME IN ('cart', 'orders', 'order_items', 'payment_transactions', 'user_addresses')
ORDER BY TABLE_NAME;

-- Test query to check foreign key constraints
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