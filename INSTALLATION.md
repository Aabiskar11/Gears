# 🚀 Gears Project - Database Setup Guide

## Quick Setup (Recommended)

### Step 1: Start XAMPP
1. Open XAMPP Control Panel
2. Click "Start" for **Apache** and **MySQL**
3. Wait for both services to show green status

### Step 2: Automatic Database Setup
1. Open your web browser
2. Go to: `http://localhost/Gears/setup_database.php`
3. The script will automatically:
   - Create the `gears_db` database
   - Create all necessary tables
   - Insert sample data
   - Show success message

### Step 3: Test Connection
1. Go to: `http://localhost/Gears/test_connection.php`
2. Verify that all tables are created and contain data

### Step 4: Access Your Website
1. Go to: `http://localhost/Gears/`
2. Your website is now fully functional!

---

## Manual Setup (Alternative)

### Option 1: Using phpMyAdmin
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click "Import" in the top menu
3. Choose file: `database/setup.sql`
4. Click "Go" to import

### Option 2: Using MySQL Command Line
1. Open MySQL command line or phpMyAdmin SQL tab
2. Copy and paste the contents of `database/setup.sql`
3. Execute the SQL commands

---

## Database Structure

### Tables Created:
- **`users`** - User accounts and authentication
- **`categories`** - Product categories (6 categories)
- **`products`** - Product catalog (18 sample products)

### Sample Data:
- 6 product categories (Power Tools, Heavy Machinery, etc.)
- 18 industrial products with realistic pricing
- Featured products for homepage display

---

## Connection Details

### Database Configuration (`config/database.php`):
```php
$host = 'localhost';
$dbname = 'gears_db';
$username = 'root';
$password = '';
```

### Default XAMPP Settings:
- **Host:** localhost
- **Username:** root
- **Password:** (empty)
- **Database:** gears_db

---

## Troubleshooting

### ❌ "Connection failed" Error
**Solution:**
1. Make sure XAMPP MySQL is running
2. Check if port 3306 is available
3. Verify database credentials in `config/database.php`

### ❌ "Database doesn't exist" Error
**Solution:**
1. Run `setup_database.php` first
2. Or manually create database in phpMyAdmin

### ❌ "Table doesn't exist" Error
**Solution:**
1. Run `setup_database.php` to create all tables
2. Or import `database/setup.sql` manually

### ❌ "Access denied" Error
**Solution:**
1. Check MySQL username/password
2. Ensure MySQL service is running
3. Try restarting XAMPP

---

## Verification Steps

After setup, you should see:

### In `test_connection.php`:
- ✅ Database connection successful
- Users: 0 records (empty initially)
- Categories: 6 records
- Products: 18 records

### In Your Website:
- Registration form works
- Login form works
- Products display on homepage
- Category filtering works
- User sessions work

---

## Next Steps

1. **Test Registration:** Create a new user account
2. **Test Login:** Login with your credentials
3. **Browse Products:** Navigate through categories
4. **Customize:** Add your own products via database

---

## Support

If you encounter issues:
1. Check XAMPP error logs
2. Verify all services are running
3. Test database connection with `test_connection.php`
4. Ensure file permissions are correct

**Your Gears website is now ready to use! 🎉** 