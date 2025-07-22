-- Create database
CREATE DATABASE IF NOT EXISTS gears_db;
USE gears_db;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create products table
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
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Insert default categories
INSERT INTO categories (name, slug, description) VALUES
('Power Tools', 'power-tools', 'Electric and battery-powered tools for various applications'),
('Heavy Machinery', 'heavy-machinery', 'Large industrial machinery and equipment'),
('Safety Equipment', 'safety-equipment', 'Personal protective equipment and safety gear'),
('Construction Tools', 'construction-tools', 'Tools specifically for construction work'),
('Woodworking', 'woodworking', 'Tools and equipment for woodworking projects'),
('Metalworking', 'metalworking', 'Tools and equipment for metal fabrication and working');

-- Insert sample products
INSERT INTO products (name, description, price, category_id, image_url, stock_quantity, is_featured) VALUES
('Heavy Duty Industrial Drill', 'Professional grade industrial drill with high torque and durability', 249.99, 1, 'https://via.placeholder.com/250x200', 50, TRUE),
('Professional Circular Saw', 'High-performance circular saw for professional woodworking', 179.99, 1, 'https://via.placeholder.com/250x200', 30, TRUE),
('Industrial Angle Grinder', 'Heavy-duty angle grinder for metal cutting and grinding', 129.99, 1, 'https://via.placeholder.com/250x200', 40, TRUE),
('Heavy Duty Air Compressor', 'Industrial air compressor for pneumatic tools', 499.99, 2, 'https://via.placeholder.com/250x200', 15, TRUE),
('Cordless Hammer Drill', '18V cordless hammer drill with lithium-ion battery', 199.99, 1, 'https://via.placeholder.com/250x200', 25, FALSE),
('Rotary Hammer', 'Professional rotary hammer for concrete drilling', 279.99, 1, 'https://via.placeholder.com/250x200', 20, FALSE),
('Impact Driver', 'High-torque impact driver for fastening applications', 149.99, 1, 'https://via.placeholder.com/250x200', 35, FALSE),
('Jigsaw', 'Variable speed jigsaw for curved cuts', 89.99, 1, 'https://via.placeholder.com/250x200', 30, FALSE),
('Orbital Sander', 'Random orbital sander for smooth finishing', 79.99, 5, 'https://via.placeholder.com/250x200', 25, FALSE),
('Table Saw', 'Professional table saw with safety features', 349.99, 5, 'https://via.placeholder.com/250x200', 10, FALSE),
('Miter Saw', 'Compound miter saw for precise angle cuts', 299.99, 5, 'https://via.placeholder.com/250x200', 15, FALSE),
('Planer', 'Electric planer for wood surface preparation', 159.99, 5, 'https://via.placeholder.com/250x200', 20, FALSE),
('Safety Helmet', 'Industrial safety helmet with adjustable suspension', 45.99, 3, 'https://via.placeholder.com/250x200', 100, FALSE),
('Safety Gloves', 'Heavy-duty work gloves for hand protection', 29.99, 3, 'https://via.placeholder.com/250x200', 150, FALSE),
('Safety Goggles', 'Anti-fog safety goggles for eye protection', 19.99, 3, 'https://via.placeholder.com/250x200', 200, FALSE),
('Welding Helmet', 'Auto-darkening welding helmet', 89.99, 6, 'https://via.placeholder.com/250x200', 25, FALSE),
('Metal Cutting Saw', 'Heavy-duty metal cutting circular saw', 399.99, 6, 'https://via.placeholder.com/250x200', 12, FALSE),
('Concrete Mixer', 'Portable concrete mixer for construction', 599.99, 4, 'https://via.placeholder.com/250x200', 8, FALSE); 