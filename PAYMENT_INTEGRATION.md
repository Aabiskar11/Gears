# Payment Integration Guide for Gears E-commerce

This guide provides instructions for integrating real payment gateways into the Gears e-commerce system.

## Current Implementation

The current system includes a simulated payment processing system in `process_order.php` that demonstrates the flow but doesn't connect to real payment gateways.

## Supported Payment Methods

### 1. Credit Card Processing

#### Stripe Integration (Recommended)

**Step 1: Install Stripe PHP SDK**
```bash
composer require stripe/stripe-php
```

**Step 2: Create Stripe Configuration**
Create `config/stripe.php`:
```php
<?php
// Stripe configuration
define('STRIPE_SECRET_KEY', 'sk_test_your_stripe_secret_key');
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_your_stripe_publishable_key');

// Initialize Stripe
require_once 'vendor/autoload.php';
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
?>
```

**Step 3: Update Payment Processing**
Replace the `processCreditCardPayment` function in `process_order.php`:

```php
function processCreditCardPayment($order_id, $amount, $payment_data) {
    require_once 'config/stripe.php';
    
    try {
        // Create payment intent
        $payment_intent = \Stripe\PaymentIntent::create([
            'amount' => $amount * 100, // Convert to cents
            'currency' => 'usd',
            'payment_method_data' => [
                'type' => 'card',
                'card' => [
                    'token' => $payment_data['stripe_token']
                ]
            ],
            'confirm' => true,
            'return_url' => 'https://yourdomain.com/order_success.php',
            'metadata' => [
                'order_id' => $order_id
            ]
        ]);
        
        if ($payment_intent->status === 'succeeded') {
            // Save transaction details
            saveTransaction($order_id, $payment_intent->id, $amount, 'credit_card', 'completed');
            
            return [
                'success' => true,
                'transaction_id' => $payment_intent->id,
                'message' => 'Payment processed successfully'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Payment failed: ' . $payment_intent->last_payment_error->message
            ];
        }
    } catch (\Stripe\Exception\CardException $e) {
        return [
            'success' => false,
            'message' => 'Card error: ' . $e->getMessage()
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Payment error: ' . $e->getMessage()
        ];
    }
}
```

**Step 4: Add Stripe Elements to Checkout**
Update `checkout.php` to include Stripe Elements:

```html
<!-- Add to head section -->
<script src="https://js.stripe.com/v3/"></script>

<!-- Replace credit card fields with Stripe Elements -->
<div id="credit-card-fields" style="display: none;">
    <div id="card-element" style="padding: 12px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 20px;"></div>
    <div id="card-errors" style="color: red; margin-bottom: 20px;"></div>
</div>

<script>
// Initialize Stripe
const stripe = Stripe('pk_test_your_stripe_publishable_key');
const elements = stripe.elements();
const card = elements.create('card');
card.mount('#card-element');

// Handle form submission
document.getElementById('checkout-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    stripe.createToken(card).then(function(result) {
        if (result.error) {
            document.getElementById('card-errors').textContent = result.error.message;
        } else {
            // Add token to form
            const hiddenInput = document.createElement('input');
            hiddenInput.setAttribute('type', 'hidden');
            hiddenInput.setAttribute('name', 'stripe_token');
            hiddenInput.setAttribute('value', result.token.id);
            document.getElementById('checkout-form').appendChild(hiddenInput);
            
            // Submit form
            document.getElementById('checkout-form').submit();
        }
    });
});
</script>
```

#### PayPal Integration

**Step 1: Install PayPal SDK**
```bash
composer require paypal/rest-api-sdk-php
```

**Step 2: Create PayPal Configuration**
Create `config/paypal.php`:
```php
<?php
// PayPal configuration
define('PAYPAL_CLIENT_ID', 'your_paypal_client_id');
define('PAYPAL_CLIENT_SECRET', 'your_paypal_client_secret');
define('PAYPAL_MODE', 'sandbox'); // Change to 'live' for production

// Initialize PayPal
require_once 'vendor/autoload.php';
$apiContext = new \PayPal\Rest\ApiContext(
    new \PayPal\Auth\OAuthTokenCredential(PAYPAL_CLIENT_ID, PAYPAL_CLIENT_SECRET)
);
$apiContext->setConfig([
    'mode' => PAYPAL_MODE,
    'log.LogEnabled' => true,
    'log.FileName' => 'logs/paypal.log',
    'log.LogLevel' => 'INFO'
]);
?>
```

**Step 3: Update PayPal Processing**
Replace the `processPayPalPayment` function:

```php
function processPayPalPayment($order_id, $amount, $payment_data) {
    require_once 'config/paypal.php';
    
    try {
        $payment = new \PayPal\Api\Payment();
        $payment->setIntent('sale');
        
        $item = new \PayPal\Api\Item();
        $item->setName('Gears Order #' . $order_id)
             ->setCurrency('USD')
             ->setQuantity(1)
             ->setPrice($amount);
        
        $itemList = new \PayPal\Api\ItemList();
        $itemList->setItems([$item]);
        
        $amount_details = new \PayPal\Api\Amount();
        $amount_details->setCurrency('USD')
                      ->setTotal($amount);
        
        $transaction = new \PayPal\Api\Transaction();
        $transaction->setAmount($amount_details)
                   ->setItemList($itemList)
                   ->setDescription('Gears Industrial Equipment Order')
                   ->setInvoiceNumber($order_id);
        
        $payment->setTransactions([$transaction]);
        
        $redirectUrls = new \PayPal\Api\RedirectUrls();
        $redirectUrls->setReturnUrl('https://yourdomain.com/paypal_success.php')
                    ->setCancelUrl('https://yourdomain.com/paypal_cancel.php');
        $payment->setRedirectUrls($redirectUrls);
        
        $payment->create($apiContext);
        
        // Save transaction details
        saveTransaction($order_id, $payment->getId(), $amount, 'paypal', 'pending');
        
        return [
            'success' => true,
            'transaction_id' => $payment->getId(),
            'approval_url' => $payment->getApprovalLink(),
            'message' => 'PayPal payment initiated'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'PayPal error: ' . $e->getMessage()
        ];
    }
}
```

### 2. Alternative Payment Methods

#### Square Integration

**Step 1: Install Square SDK**
```bash
composer require square/square
```

**Step 2: Configuration**
```php
<?php
// Square configuration
define('SQUARE_ACCESS_TOKEN', 'your_square_access_token');
define('SQUARE_LOCATION_ID', 'your_square_location_id');

$client = new \Square\SquareClient([
    'accessToken' => SQUARE_ACCESS_TOKEN,
    'environment' => 'sandbox' // Change to 'production' for live
]);
?>
```

#### Razorpay Integration (for Indian market)

**Step 1: Install Razorpay SDK**
```bash
composer require razorpay/razorpay
```

**Step 2: Configuration**
```php
<?php
// Razorpay configuration
define('RAZORPAY_KEY_ID', 'your_razorpay_key_id');
define('RAZORPAY_KEY_SECRET', 'your_razorpay_key_secret');

$api = new Razorpay\Api\Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
?>
```

## Security Best Practices

### 1. PCI Compliance
- Never store credit card data in your database
- Use tokenization provided by payment gateways
- Implement proper SSL/TLS encryption
- Regular security audits

### 2. Data Protection
```php
// Example of secure payment data handling
function sanitizePaymentData($data) {
    $allowed_fields = ['payment_method', 'amount', 'currency'];
    $sanitized = [];
    
    foreach ($allowed_fields as $field) {
        if (isset($data[$field])) {
            $sanitized[$field] = htmlspecialchars(strip_tags($data[$field]));
        }
    }
    
    return $sanitized;
}
```

### 3. Error Handling
```php
function handlePaymentError($error, $order_id) {
    // Log error securely
    error_log("Payment error for order $order_id: " . $error);
    
    // Update order status
    updateOrderStatus($order_id, 'payment_failed');
    
    // Notify admin
    sendAdminNotification("Payment failed for order $order_id");
    
    return [
        'success' => false,
        'message' => 'Payment processing error. Please try again.'
    ];
}
```

## Testing

### 1. Test Cards (Stripe)
- Visa: 4242424242424242
- Mastercard: 5555555555554444
- American Express: 378282246310005

### 2. Test Scenarios
```php
// Test payment processing
function testPaymentProcessing() {
    $test_cases = [
        ['amount' => 100, 'expected' => 'success'],
        ['amount' => 0, 'expected' => 'error'],
        ['amount' => -100, 'expected' => 'error']
    ];
    
    foreach ($test_cases as $test) {
        $result = processPayment(1, $test['amount'], 'credit_card', []);
        assert($result['success'] === ($test['expected'] === 'success'));
    }
}
```

## Production Deployment

### 1. Environment Variables
```bash
# .env file
STRIPE_SECRET_KEY=sk_live_your_live_key
STRIPE_PUBLISHABLE_KEY=pk_live_your_live_key
PAYPAL_CLIENT_ID=your_live_paypal_client_id
PAYPAL_CLIENT_SECRET=your_live_paypal_client_secret
```

### 2. SSL Certificate
- Install valid SSL certificate
- Force HTTPS redirects
- Update payment gateway webhook URLs

### 3. Monitoring
```php
// Payment monitoring
function monitorPaymentSuccess() {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as success_count, 
               COUNT(CASE WHEN payment_status = 'failed' THEN 1 END) as failed_count
        FROM orders 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ");
    $stmt->execute();
    $stats = $stmt->fetch();
    
    if ($stats['failed_count'] > $stats['success_count'] * 0.1) {
        sendAdminAlert("High payment failure rate detected");
    }
}
```

## Webhook Integration

### 1. Stripe Webhooks
```php
// webhook_handler.php
<?php
require_once 'config/stripe.php';

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;

try {
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, 'whsec_your_webhook_secret'
    );
} catch(\UnexpectedValueException $e) {
    http_response_code(400);
    exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
    http_response_code(400);
    exit();
}

switch ($event->type) {
    case 'payment_intent.succeeded':
        $paymentIntent = $event->data->object;
        updateOrderPaymentStatus($paymentIntent->metadata->order_id, 'paid');
        break;
    case 'payment_intent.payment_failed':
        $paymentIntent = $event->data->object;
        updateOrderPaymentStatus($paymentIntent->metadata->order_id, 'failed');
        break;
}

http_response_code(200);
?>
```

## Troubleshooting

### Common Issues

1. **Payment Declined**
   - Check card details
   - Verify billing address
   - Ensure sufficient funds

2. **Webhook Failures**
   - Verify webhook URL
   - Check SSL certificate
   - Review server logs

3. **Database Errors**
   - Check database connection
   - Verify table structure
   - Review transaction logs

### Debug Mode
```php
// Enable debug mode for development
define('PAYMENT_DEBUG', true);

if (PAYMENT_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
```

## Support

For payment gateway specific support:
- Stripe: https://support.stripe.com/
- PayPal: https://developer.paypal.com/support/
- Square: https://developer.squareup.com/support
- Razorpay: https://razorpay.com/support/

## Next Steps

1. Choose your preferred payment gateway
2. Set up developer accounts
3. Implement the integration code
4. Test thoroughly in sandbox mode
5. Deploy to production
6. Monitor and maintain

Remember to always follow the payment gateway's security guidelines and keep your integration updated with the latest SDK versions. 