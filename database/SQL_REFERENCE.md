# 🗄️ Gears Database - SQL Reference Guide

## 📋 Quick Database Setup

### 1. Create Database
```sql
CREATE DATABASE IF NOT EXISTS gears_db;
USE gears_db;
```

### 2. Import Complete Database
```bash
# Method 1: Using phpMyAdmin
# Import file: database/complete_database.sql

# Method 2: Using command line
mysql -u root -p < database/complete_database.sql
```

---

## 🏗️ Table Structures

### Users Table
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Categories Table
```sql
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Products Table
```sql
CREATE TABLE products (
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
```

---

## 📊 Sample Data

### Categories
```sql
INSERT INTO categories (name, slug, description) VALUES
('Power Tools', 'power-tools', 'Electric and battery-powered tools'),
('Heavy Machinery', 'heavy-machinery', 'Large industrial machinery'),
('Safety Equipment', 'safety-equipment', 'Personal protective equipment'),
('Construction Tools', 'construction-tools', 'Construction-specific tools'),
('Woodworking', 'woodworking', 'Woodworking tools and equipment'),
('Metalworking', 'metalworking', 'Metal fabrication tools');
```

### Sample Products
```sql
INSERT INTO products (name, description, price, category_id, stock_quantity, is_featured) VALUES
('Heavy Duty Industrial Drill', 'Professional grade drill', 249.99, 1, 50, TRUE),
('Professional Circular Saw', 'High-performance saw', 179.99, 1, 30, TRUE),
('Safety Helmet', 'Industrial safety helmet', 45.99, 3, 100, FALSE);
```

---

## 🔍 Common Queries

### View All Products
```sql
SELECT p.*, c.name as category_name 
FROM products p 
LEFT JOIN categories c ON p.category_id = c.id;
```

### Featured Products
```sql
SELECT * FROM products WHERE is_featured = TRUE;
```

### Products by Category
```sql
SELECT p.*, c.name as category_name 
FROM products p 
LEFT JOIN categories c ON p.category_id = c.id 
WHERE c.slug = 'power-tools';
```

### Low Stock Products
```sql
SELECT * FROM products WHERE stock_quantity < 20;
```

### Price Range by Category
```sql
SELECT 
    c.name,
    MIN(p.price) as min_price,
    MAX(p.price) as max_price,
    AVG(p.price) as avg_price
FROM categories c
LEFT JOIN products p ON c.id = p.category_id
GROUP BY c.id;
```

### Recent Products
```sql
SELECT * FROM products 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY);
```

---

## 👥 User Management

### Register New User
```sql
INSERT INTO users (fullname, email, password) 
VALUES ('John Doe', 'john@example.com', 'hashed_password');
```

### Find User by Email
```sql
SELECT * FROM users WHERE email = 'john@example.com';
```

### Update User
```sql
UPDATE users 
SET fullname = 'John Smith', email = 'johnsmith@example.com' 
WHERE id = 1;
```

### Delete User
```sql
DELETE FROM users WHERE id = 1;
```

---

## 🛠️ Product Management

### Add New Product
```sql
INSERT INTO products (name, description, price, category_id, stock_quantity) 
VALUES ('New Tool', 'Description', 99.99, 1, 25);
```

### Update Product Price
```sql
UPDATE products SET price = 129.99 WHERE id = 1;
```

### Update Stock Quantity
```sql
UPDATE products SET stock_quantity = stock_quantity - 1 WHERE id = 1;
```

### Mark as Featured
```sql
UPDATE products SET is_featured = TRUE WHERE id = 1;
```

### Delete Product
```sql
DELETE FROM products WHERE id = 1;
```

---

## 📈 Analytics Queries

### Total Products by Category
```sql
SELECT 
    c.name,
    COUNT(p.id) as product_count
FROM categories c
LEFT JOIN products p ON c.id = p.category_id
GROUP BY c.id;
```

### Average Price by Category
```sql
SELECT 
    c.name,
    AVG(p.price) as avg_price
FROM categories c
LEFT JOIN products p ON c.id = p.category_id
GROUP BY c.id;
```

### Stock Value by Category
```sql
SELECT 
    c.name,
    SUM(p.price * p.stock_quantity) as total_value
FROM categories c
LEFT JOIN products p ON c.id = p.category_id
GROUP BY c.id;
```

### Database Statistics
```sql
SELECT 
    'Users' as metric, COUNT(*) as count FROM users
UNION ALL
SELECT 'Categories', COUNT(*) FROM categories
UNION ALL
SELECT 'Products', COUNT(*) FROM products
UNION ALL
SELECT 'Featured Products', COUNT(*) FROM products WHERE is_featured = TRUE;
```

---

## 🔧 Maintenance Queries

### Backup Database
```bash
mysqldump -u root -p gears_db > backup.sql
```

### Restore Database
```bash
mysql -u root -p gears_db < backup.sql
```

### Reset Auto-increment
```sql
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE categories AUTO_INCREMENT = 1;
ALTER TABLE products AUTO_INCREMENT = 1;
```

### Clean Empty Categories
```sql
DELETE FROM categories 
WHERE id NOT IN (SELECT DISTINCT category_id FROM products WHERE category_id IS NOT NULL);
```

---

## 🚨 Important Notes

### Security
- Always use prepared statements in PHP
- Hash passwords before storing
- Validate input data
- Use proper indexes for performance

### Performance
- Add indexes on frequently queried columns
- Use LIMIT for large result sets
- Optimize queries with EXPLAIN

### Backup
- Regular database backups
- Test restore procedures
- Keep multiple backup copies

---

## 📞 Support Queries

### Check Database Connection
```sql
SELECT 1;
```

### Show All Tables
```sql
SHOW TABLES;
```

### Show Table Structure
```sql
DESCRIBE users;
DESCRIBE categories;
DESCRIBE products;
```

### Show Database Size
```sql
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.tables 
WHERE table_schema = 'gears_db';
``` 