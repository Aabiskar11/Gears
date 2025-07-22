-- =====================================================
-- CART AND PAYMENT SYSTEM - DATABASE TABLES
-- =====================================================

USE gears_db;

-- =====================================================
-- CART SYSTEM TABLES
-- =====================================================

-- Shopping cart table
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id),
    INDEX idx_user_id (user_id),
    INDEX idx_product_id (product_id)
);

-- Orders table
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
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_order_number (order_number),
    INDEX idx_status (status),
    INDEX idx_payment_status (payment_status)
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    product_price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id)
);

-- Payment transactions table
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
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_status (status)
);

-- User addresses table
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
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_address_type (address_type)
);

-- =====================================================
-- SAMPLE DATA FOR TESTING
-- =====================================================

-- Sample user addresses (assuming user_id = 1 exists)
INSERT INTO user_addresses (user_id, address_type, first_name, last_name, company, address_line1, city, state, postal_code, country, phone, is_default) VALUES
(1, 'both', 'John', 'Doe', 'Industrial Solutions Inc.', '123 Main Street', 'New York', 'NY', '10001', 'United States', '+1-555-123-4567', TRUE),
(1, 'shipping', 'John', 'Doe', 'Industrial Solutions Inc.', '456 Warehouse Ave', 'Los Angeles', 'CA', '90210', 'United States', '+1-555-987-6543', FALSE);

-- =====================================================
-- USEFUL QUERIES FOR CART SYSTEM
-- =====================================================

-- Get user's cart with product details
SELECT 
    c.id as cart_id,
    c.quantity,
    p.id as product_id,
    p.name as product_name,
    p.price,
    p.image_url,
    (c.quantity * p.price) as subtotal
FROM cart c
JOIN products p ON c.product_id = p.id
WHERE c.user_id = 1;  -- Change user_id as needed

-- Get cart total for a user
SELECT 
    SUM(c.quantity * p.price) as cart_total,
    COUNT(c.id) as item_count
FROM cart c
JOIN products p ON c.product_id = p.id
WHERE c.user_id = 1;  -- Change user_id as needed

-- Get order details with items
SELECT 
    o.id as order_id,
    o.order_number,
    o.total_amount,
    o.status,
    o.payment_status,
    o.created_at,
    oi.product_name,
    oi.quantity,
    oi.subtotal
FROM orders o
JOIN order_items oi ON o.id = oi.order_id
WHERE o.user_id = 1  -- Change user_id as needed
ORDER BY o.created_at DESC;

-- Get payment transaction history
SELECT 
    pt.transaction_id,
    pt.amount,
    pt.payment_method,
    pt.status,
    pt.created_at,
    o.order_number
FROM payment_transactions pt
JOIN orders o ON pt.order_id = o.id
WHERE o.user_id = 1  -- Change user_id as needed
ORDER BY pt.created_at DESC;

-- =====================================================
-- STORED PROCEDURES FOR COMMON OPERATIONS
-- =====================================================

DELIMITER //

-- Procedure to add item to cart
CREATE PROCEDURE AddToCart(
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
CREATE PROCEDURE GenerateOrderNumber(OUT order_number VARCHAR(50))
BEGIN
    DECLARE timestamp_part VARCHAR(20);
    DECLARE random_part VARCHAR(10);
    
    SET timestamp_part = DATE_FORMAT(NOW(), '%Y%m%d%H%i%s');
    SET random_part = LPAD(FLOOR(RAND() * 10000), 4, '0');
    SET order_number = CONCAT('ORD-', timestamp_part, '-', random_part);
END //

-- Procedure to create order from cart
CREATE PROCEDURE CreateOrderFromCart(
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

-- Trigger to update product stock when order is created
DELIMITER //
CREATE TRIGGER after_order_item_insert
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
CREATE VIEW user_cart_summary AS
SELECT 
    c.user_id,
    COUNT(c.id) as item_count,
    SUM(c.quantity) as total_quantity,
    SUM(c.quantity * p.price) as total_amount
FROM cart c
JOIN products p ON c.product_id = p.id
GROUP BY c.user_id;

-- View for order summary
CREATE VIEW order_summary AS
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