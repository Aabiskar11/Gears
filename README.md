# Gears - Industrial Equipment Marketplace

A complete PHP-based e-commerce website for industrial equipment and machinery with full shopping cart and payment processing capabilities.

## Features

- **User Authentication**: Registration and login system with secure password hashing
- **Product Catalog**: Browse products by categories with dynamic filtering
- **Shopping Cart**: Full cart functionality with add, update, and remove items
- **Checkout System**: Complete checkout process with address collection
- **Payment Processing**: Integrated payment system with multiple payment methods
- **Order Management**: Order tracking and management system
- **Dynamic Content**: Products loaded from MySQL database
- **Responsive Design**: Modern, mobile-friendly interface
- **Session Management**: Secure user sessions
- **Category Filtering**: Filter products by different categories

## Database Setup

### Prerequisites
- XAMPP (or similar local server with MySQL and PHP)
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Installation Steps

1. **Start XAMPP**
   - Start Apache and MySQL services

2. **Create Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the `database/complete_database.sql` file
   - This will create the `gears_db` database with all necessary tables and sample data

3. **Setup Cart System**
   - Run `setup_cart_system.php` in your browser
   - This will create the cart and payment system tables
   - Or manually import `database/cart_system.sql`

4. **Configure Database Connection**
   - Edit `config/database.php` if needed
   - Default settings:
     - Host: localhost
     - Database: gears_db
     - Username: root
     - Password: (empty)

## File Structure

```
Gears/
├── config/
│   └── database.php          # Database configuration
├── database/
│   ├── complete_database.sql # Main database schema and sample data
│   ├── cart_system.sql       # Cart and payment system tables
│   └── SQL_REFERENCE.md      # Database reference documentation
├── image/                    # Product images
├── index.php                # Landing page
├── landingpage.php          # Main homepage with featured products
├── login.php               # Login form
├── login_handler.php       # Login processing
├── logout.php              # Logout functionality
├── product.php             # Product catalog page with cart functionality
├── register_handler.php    # Registration processing
├── signin.php             # Registration form
├── cart.php               # Shopping cart page
├── cart_handler.php       # Cart AJAX handler
├── checkout.php           # Checkout page
├── process_order.php      # Order processing and payment
├── order_success.php      # Order confirmation page
├── setup_cart_system.php  # Cart system setup script
├── PAYMENT_INTEGRATION.md # Payment gateway integration guide
└── README.md              # This file
```

## Database Schema

### Core Tables

#### Users Table
- `id` - Primary key
- `fullname` - User's full name
- `email` - Unique email address
- `password` - Hashed password
- `created_at` - Registration timestamp
- `updated_at` - Last update timestamp

#### Categories Table
- `id` - Primary key
- `name` - Category name
- `slug` - URL-friendly category identifier
- `description` - Category description
- `created_at` - Creation timestamp

#### Products Table
- `id` - Primary key
- `name` - Product name
- `description` - Product description
- `price` - Product price
- `category_id` - Foreign key to categories
- `image_url` - Product image URL
- `stock_quantity` - Available stock
- `is_featured` - Featured product flag
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

### Cart System Tables

#### Cart Table
- `id` - Primary key
- `user_id` - Foreign key to users
- `product_id` - Foreign key to products
- `quantity` - Item quantity
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

#### Orders Table
- `id` - Primary key
- `user_id` - Foreign key to users
- `order_number` - Unique order identifier
- `total_amount` - Order total
- `shipping_address` - Shipping address
- `billing_address` - Billing address
- `status` - Order status (pending, processing, shipped, delivered, cancelled)
- `payment_status` - Payment status (pending, paid, failed, refunded)
- `payment_method` - Payment method used
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

#### Order Items Table
- `id` - Primary key
- `order_id` - Foreign key to orders
- `product_id` - Foreign key to products
- `product_name` - Product name at time of order
- `product_price` - Product price at time of order
- `quantity` - Item quantity
- `subtotal` - Line item total
- `created_at` - Creation timestamp

#### Payment Transactions Table
- `id` - Primary key
- `order_id` - Foreign key to orders
- `transaction_id` - Payment gateway transaction ID
- `amount` - Transaction amount
- `currency` - Transaction currency
- `payment_method` - Payment method
- `status` - Transaction status
- `gateway_response` - Payment gateway response
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

#### User Addresses Table
- `id` - Primary key
- `user_id` - Foreign key to users
- `address_type` - Address type (shipping, billing, both)
- `first_name` - First name
- `last_name` - Last name
- `company` - Company name
- `address_line1` - Address line 1
- `address_line2` - Address line 2
- `city` - City
- `state` - State/Province
- `postal_code` - Postal code
- `country` - Country
- `phone` - Phone number
- `is_default` - Default address flag
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

## Usage

### Basic Navigation
1. **Access the Website**
   - Navigate to `http://localhost/Gears/`

2. **User Registration**
   - Click "Sign in" to create a new account
   - Fill in your details and submit

3. **User Login**
   - Click "Login" to access your account
   - Use your email and password

4. **Browse Products**
   - View featured products on the homepage
   - Click "Shop" to see all products
   - Filter by categories using the category cards

### Shopping Cart Features
1. **Add to Cart**
   - Click "Add to Cart" on any product
   - Cart count updates in navigation
   - Items are saved to your account

2. **Manage Cart**
   - Click the cart icon to view your cart
   - Update quantities or remove items
   - View order summary with tax calculation

3. **Checkout Process**
   - Click "Proceed to Checkout" from cart
   - Fill in shipping and billing addresses
   - Select payment method (Credit Card or PayPal)
   - Complete payment (simulated for demo)

4. **Order Confirmation**
   - View order details and confirmation
   - Receive order number for tracking
   - Continue shopping or view orders

## Payment Integration

The system includes a comprehensive payment integration framework:

### Current Implementation
- Simulated payment processing for demonstration
- Support for credit card and PayPal methods
- Secure payment data handling
- Transaction logging and tracking

### Real Payment Gateway Integration
See `PAYMENT_INTEGRATION.md` for detailed instructions on integrating:
- **Stripe** (Recommended)
- **PayPal**
- **Square**
- **Razorpay** (for Indian market)

### Security Features
- PCI compliance guidelines
- Secure data handling
- SSL/TLS encryption requirements
- Webhook integration for payment status updates

## Security Features

- **Password Hashing**: Passwords are hashed using PHP's `password_hash()`
- **SQL Injection Prevention**: Prepared statements used throughout
- **XSS Prevention**: Output is properly escaped using `htmlspecialchars()`
- **Session Security**: Secure session management
- **Input Validation**: Form inputs are validated and sanitized
- **Payment Security**: PCI-compliant payment processing
- **CSRF Protection**: Form token validation

## Customization

### Adding New Products
1. Access the database through phpMyAdmin
2. Insert new records into the `products` table
3. Set `is_featured = 1` to make it appear on the homepage

### Adding New Categories
1. Insert new records into the `categories` table
2. Update the category links in the HTML files

### Payment Methods
1. Follow the `PAYMENT_INTEGRATION.md` guide
2. Choose your preferred payment gateway
3. Update the payment processing functions
4. Test thoroughly in sandbox mode

### Styling
- All styles are inline CSS for easy modification
- Color scheme uses crimson (#a50c2a) as the primary color
- Responsive design with flexbox and CSS Grid

## API Endpoints

### Cart Management
- `POST cart_handler.php?action=add` - Add item to cart
- `POST cart_handler.php?action=update` - Update cart item quantity
- `POST cart_handler.php?action=remove` - Remove item from cart
- `GET cart_handler.php?action=get` - Get cart contents
- `POST cart_handler.php?action=clear` - Clear entire cart

### Order Processing
- `POST process_order.php` - Process checkout and payment
- `GET order_success.php` - Order confirmation page

## Troubleshooting

### Database Connection Issues
- Ensure MySQL is running in XAMPP
- Check database credentials in `config/database.php`
- Verify the `gears_db` database exists

### Cart System Issues
- Run `setup_cart_system.php` to ensure all tables are created
- Check if user is logged in (cart requires authentication)
- Verify cart tables exist in database

### Payment Issues
- Check payment gateway configuration
- Verify SSL certificate is installed
- Review payment gateway logs
- Test with sandbox/test credentials first

### Page Not Found Errors
- Ensure Apache is running in XAMPP
- Check file permissions
- Verify all files are in the correct directory

### Login Issues
- Clear browser cookies and cache
- Check if the user exists in the database
- Verify password was entered correctly

## Testing

### Cart Functionality
1. Register a new account
2. Add items to cart from product page
3. View cart and modify quantities
4. Proceed through checkout
5. Complete payment process

### Payment Testing
- Use test credit card numbers provided in `PAYMENT_INTEGRATION.md`
- Test both success and failure scenarios
- Verify order status updates correctly

## Future Enhancements

- Admin panel for product and order management
- Advanced product search and filtering
- User reviews and ratings system
- Email notifications for orders
- Product image upload functionality
- Inventory management system
- Shipping calculator integration
- Multi-language support
- Mobile app development

## Support

For issues or questions, please check:
1. XAMPP error logs
2. PHP error logs
3. Database connection settings
4. File permissions
5. Payment gateway documentation
6. Cart system setup verification

## License

This project is for educational purposes. Feel free to modify and use as needed.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## Acknowledgments

- XAMPP for local development environment
- Payment gateway providers for their APIs
- PHP community for excellent documentation 