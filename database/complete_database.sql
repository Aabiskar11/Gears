-- =====================================================
-- GEARS PROJECT - COMPLETE DATABASE SQL
-- Industrial Equipment Marketplace Database
-- =====================================================

-- Create database
CREATE DATABASE IF NOT EXISTS gears_db;
USE gears_db;

-- =====================================================
-- TABLE STRUCTURES
-- =====================================================

-- Users table for authentication
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_created_at (created_at)
);

-- Categories table for product organization
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_name (name)
);

-- Products table for product catalog
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT,
    image_url VARCHAR(255),
    stock_quantity INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_featured (is_featured),
    INDEX idx_price (price),
    INDEX idx_stock (stock_quantity)
);

-- =====================================================
-- SAMPLE DATA - CATEGORIES
-- =====================================================

INSERT INTO categories (name, slug, description) VALUES
('Power Tools', 'power-tools', 'Electric and battery-powered tools for various applications'),
('Heavy Machinery', 'heavy-machinery', 'Large industrial machinery and equipment'),
('Safety Equipment', 'safety-equipment', 'Personal protective equipment and safety gear'),
('Construction Tools', 'construction-tools', 'Tools specifically for construction work'),
('Woodworking', 'woodworking', 'Tools and equipment for woodworking projects'),
('Metalworking', 'metalworking', 'Tools and equipment for metal fabrication and working');

-- =====================================================
-- SAMPLE DATA - PRODUCTS
-- =====================================================

INSERT INTO products (name, description, price, category_id, image_url, stock_quantity, is_featured) VALUES
-- Power Tools (category_id = 1)
('Heavy Duty Industrial Drill', 'Professional grade industrial drill with high torque and durability. Perfect for heavy-duty applications in construction and manufacturing.', 249.99, 1, 'https://via.placeholder.com/250x200', 50, TRUE),
('Professional Circular Saw', 'High-performance circular saw for professional woodworking. Features precision cutting and safety mechanisms.', 179.99, 1, 'https://via.placeholder.com/250x200', 30, TRUE),
('Industrial Angle Grinder', 'Heavy-duty angle grinder for metal cutting and grinding. Ideal for metalworking and construction projects.', 129.99, 1, 'https://via.placeholder.com/250x200', 40, TRUE),
('Cordless Hammer Drill', '18V cordless hammer drill with lithium-ion battery. Provides freedom of movement without power cords.', 199.99, 1, 'https://via.placeholder.com/250x200', 25, FALSE),
('Rotary Hammer', 'Professional rotary hammer for concrete drilling. Designed for heavy-duty concrete and masonry work.', 279.99, 1, 'https://via.placeholder.com/250x200', 20, FALSE),
('Impact Driver', 'High-torque impact driver for fastening applications. Perfect for driving screws and bolts efficiently.', 149.99, 1, 'https://via.placeholder.com/250x200', 35, FALSE),
('Jigsaw', 'Variable speed jigsaw for curved cuts. Ideal for cutting complex shapes in wood, metal, and plastic.', 89.99, 1, 'https://via.placeholder.com/250x200', 30, FALSE),

-- Heavy Machinery (category_id = 2)
('Heavy Duty Air Compressor', 'Industrial air compressor for pneumatic tools. Provides consistent air pressure for various applications.', 499.99, 2, 'https://via.placeholder.com/250x200', 15, TRUE),

-- Safety Equipment (category_id = 3)
('Safety Helmet', 'Industrial safety helmet with adjustable suspension. Meets safety standards for construction sites.', 45.99, 3, 'https://via.placeholder.com/250x200', 100, FALSE),
('Safety Gloves', 'Heavy-duty work gloves for hand protection. Provides grip and protection for various tasks.', 29.99, 3, 'https://via.placeholder.com/250x200', 150, FALSE),
('Safety Goggles', 'Anti-fog safety goggles for eye protection. Essential for protecting eyes from debris and chemicals.', 19.99, 3, 'https://via.placeholder.com/250x200', 200, FALSE),

-- Construction Tools (category_id = 4)
('Concrete Mixer', 'Portable concrete mixer for construction. Ideal for small to medium concrete projects.', 599.99, 4, 'https://via.placeholder.com/250x200', 8, FALSE),

-- Woodworking (category_id = 5)
('Orbital Sander', 'Random orbital sander for smooth finishing. Provides professional-quality surface preparation.', 79.99, 5, 'https://via.placeholder.com/250x200', 25, FALSE),
('Table Saw', 'Professional table saw with safety features. Essential tool for precise wood cutting.', 349.99, 5, 'https://via.placeholder.com/250x200', 10, FALSE),
('Miter Saw', 'Compound miter saw for precise angle cuts. Perfect for trim work and framing.', 299.99, 5, 'https://via.placeholder.com/250x200', 15, FALSE),
('Planer', 'Electric planer for wood surface preparation. Removes material to achieve smooth, flat surfaces.', 159.99, 5, 'https://via.placeholder.com/250x200', 20, FALSE),

-- Metalworking (category_id = 6)
('Welding Helmet', 'Auto-darkening welding helmet. Provides protection and visibility during welding operations.', 89.99, 6, 'https://via.placeholder.com/250x200', 25, FALSE),
('Metal Cutting Saw', 'Heavy-duty metal cutting circular saw. Designed for cutting various types of metal.', 399.99, 6, 'https://via.placeholder.com/250x200', 12, FALSE);

-- =====================================================
-- USEFUL QUERIES FOR DATABASE MANAGEMENT
-- =====================================================

-- View all categories with product count
SELECT 
    c.name as category_name,
    c.slug,
    COUNT(p.id) as product_count
FROM categories c
LEFT JOIN products p ON c.id = p.category_id
GROUP BY c.id, c.name, c.slug
ORDER BY product_count DESC;

-- View featured products
SELECT 
    p.name,
    p.price,
    p.stock_quantity,
    c.name as category_name
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
WHERE p.is_featured = TRUE
ORDER BY p.price DESC;

-- View products by category
SELECT 
    p.name,
    p.price,
    p.stock_quantity,
    p.is_featured
FROM products p
WHERE p.category_id = 1  -- Change category_id as needed
ORDER BY p.price;

-- View low stock products (less than 20 items)
SELECT 
    p.name,
    p.stock_quantity,
    c.name as category_name
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
WHERE p.stock_quantity < 20
ORDER BY p.stock_quantity;

-- View price range by category
SELECT 
    c.name as category_name,
    MIN(p.price) as min_price,
    MAX(p.price) as max_price,
    AVG(p.price) as avg_price,
    COUNT(p.id) as product_count
FROM categories c
LEFT JOIN products p ON c.id = p.category_id
GROUP BY c.id, c.name
ORDER BY avg_price DESC;

-- View recent products (last 30 days)
SELECT 
    p.name,
    p.price,
    p.created_at,
    c.name as category_name
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
ORDER BY p.created_at DESC;

-- =====================================================
-- DATABASE MAINTENANCE QUERIES
-- =====================================================

-- Update product prices by 10% increase
-- UPDATE products SET price = price * 1.10 WHERE category_id = 1;

-- Mark products as featured based on price
-- UPDATE products SET is_featured = TRUE WHERE price > 200;

-- Delete products with zero stock (be careful!)
-- DELETE FROM products WHERE stock_quantity = 0;

-- Reset auto-increment counters (if needed)
-- ALTER TABLE users AUTO_INCREMENT = 1;
-- ALTER TABLE categories AUTO_INCREMENT = 1;
-- ALTER TABLE products AUTO_INCREMENT = 1;

-- =====================================================
-- BACKUP AND RESTORE COMMANDS
-- =====================================================

-- To backup the database (run in command line):
-- mysqldump -u root -p gears_db > gears_backup.sql

-- To restore the database (run in command line):
-- mysql -u root -p gears_db < gears_backup.sql

-- =====================================================
-- DATABASE STATISTICS
-- =====================================================

-- Get database statistics
SELECT 
    'Total Users' as metric,
    COUNT(*) as count
FROM users
UNION ALL
SELECT 
    'Total Categories',
    COUNT(*)
FROM categories
UNION ALL
SELECT 
    'Total Products',
    COUNT(*)
FROM products
UNION ALL
SELECT 
    'Featured Products',
    COUNT(*)
FROM products
WHERE is_featured = TRUE
UNION ALL
SELECT 
    'Low Stock Products (< 20)',
    COUNT(*)
FROM products
WHERE stock_quantity < 20;

-- =====================================================
-- END OF DATABASE SETUP
-- ===================================================== 