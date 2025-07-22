-- =====================================================
-- SELLER AND ADMIN SYSTEM - DATABASE UPDATES
-- =====================================================

USE gears_db;

-- Add role column to users table
ALTER TABLE users ADD COLUMN role ENUM('customer', 'seller', 'admin') DEFAULT 'customer' AFTER password;

-- Add seller_id column to products table to track which seller owns each product
ALTER TABLE products ADD COLUMN seller_id INT AFTER category_id;
ALTER TABLE products ADD FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE;

-- Create seller_profiles table for additional seller information
CREATE TABLE IF NOT EXISTS seller_profiles (
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
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_approved (is_approved)
);

-- Create admin_actions table to track admin activities
CREATE TABLE IF NOT EXISTS admin_actions (
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
);

-- Insert default admin user (password: admin123)
INSERT INTO users (fullname, email, password, role) VALUES 
('Admin User', 'admin@gears.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample seller users (password: seller123)
INSERT INTO users (fullname, email, password, role) VALUES 
('John Tools', 'john@tools.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seller'),
('Sarah Equipment', 'sarah@equipment.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seller');

-- Insert seller profiles
INSERT INTO seller_profiles (user_id, business_name, business_description, contact_phone, business_address, is_approved, approved_by) VALUES
((SELECT id FROM users WHERE email = 'john@tools.com'), 'John\'s Tool Shop', 'Professional tools and equipment for construction and industrial use', '+1234567890', '123 Tool Street, Industrial District', TRUE, (SELECT id FROM users WHERE email = 'admin@gears.com')),
((SELECT id FROM users WHERE email = 'sarah@equipment.com'), 'Sarah\'s Equipment Co', 'Heavy machinery and industrial equipment supplier', '+0987654321', '456 Equipment Avenue, Business Park', TRUE, (SELECT id FROM users WHERE email = 'admin@gears.com'));

-- Update existing products to assign them to sellers
UPDATE products SET seller_id = (SELECT id FROM users WHERE email = 'john@tools.com') WHERE category_id IN (1, 4, 5);
UPDATE products SET seller_id = (SELECT id FROM users WHERE email = 'sarah@equipment.com') WHERE category_id IN (2, 3, 6);

-- Create indexes for better performance
CREATE INDEX idx_products_seller ON products(seller_id);
CREATE INDEX idx_users_role ON users(role); 