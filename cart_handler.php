<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'add':
            addToCart($pdo, $user_id);
            break;
        case 'update':
            updateCartItem($pdo, $user_id);
            break;
        case 'remove':
            removeFromCart($pdo, $user_id);
            break;
        case 'get':
            getCart($pdo, $user_id);
            break;
        case 'clear':
            clearCart($pdo, $user_id);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

function addToCart($pdo, $user_id) {
    $product_id = $_POST['product_id'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;
    
    if (!$product_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Product ID is required']);
        return;
    }
    
    // Validate product exists and has stock
    $stmt = $pdo->prepare("SELECT id, name, price, stock_quantity FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
        return;
    }
    
    if ($product['stock_quantity'] < $quantity) {
        http_response_code(400);
        echo json_encode(['error' => 'Insufficient stock']);
        return;
    }
    
    // Check if item already exists in cart
    $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing_item = $stmt->fetch();
    
    if ($existing_item) {
        // Update existing item
        $new_quantity = $existing_item['quantity'] + $quantity;
        if ($new_quantity > $product['stock_quantity']) {
            http_response_code(400);
            echo json_encode(['error' => 'Insufficient stock for requested quantity']);
            return;
        }
        
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$new_quantity, $existing_item['id']]);
    } else {
        // Add new item
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity]);
    }
    
    // Get updated cart summary
    $cart_summary = getCartSummary($pdo, $user_id);
    
    echo json_encode([
        'success' => true,
        'message' => 'Item added to cart',
        'cart_summary' => $cart_summary
    ]);
}

function updateCartItem($pdo, $user_id) {
    $cart_id = $_POST['cart_id'] ?? null;
    $quantity = $_POST['quantity'] ?? null;
    
    if (!$cart_id || !$quantity) {
        http_response_code(400);
        echo json_encode(['error' => 'Cart ID and quantity are required']);
        return;
    }
    
    if ($quantity <= 0) {
        // Remove item if quantity is 0 or negative
        removeFromCart($pdo, $user_id);
        return;
    }
    
    // Get cart item with product info
    $stmt = $pdo->prepare("
        SELECT c.id, c.quantity, p.stock_quantity, p.name 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.id = ? AND c.user_id = ?
    ");
    $stmt->execute([$cart_id, $user_id]);
    $cart_item = $stmt->fetch();
    
    if (!$cart_item) {
        http_response_code(404);
        echo json_encode(['error' => 'Cart item not found']);
        return;
    }
    
    if ($quantity > $cart_item['stock_quantity']) {
        http_response_code(400);
        echo json_encode(['error' => 'Insufficient stock for requested quantity']);
        return;
    }
    
    // Update quantity
    $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$quantity, $cart_id]);
    
    // Get updated cart summary
    $cart_summary = getCartSummary($pdo, $user_id);
    
    echo json_encode([
        'success' => true,
        'message' => 'Cart updated',
        'cart_summary' => $cart_summary
    ]);
}

function removeFromCart($pdo, $user_id) {
    $cart_id = $_POST['cart_id'] ?? $_GET['cart_id'] ?? null;
    
    if (!$cart_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Cart ID is required']);
        return;
    }
    
    // Verify cart item belongs to user
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $result = $stmt->execute([$cart_id, $user_id]);
    
    if ($stmt->rowCount() == 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Cart item not found']);
        return;
    }
    
    // Get updated cart summary
    $cart_summary = getCartSummary($pdo, $user_id);
    
    echo json_encode([
        'success' => true,
        'message' => 'Item removed from cart',
        'cart_summary' => $cart_summary
    ]);
}

function getCart($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT 
            c.id as cart_id,
            c.quantity,
            p.id as product_id,
            p.name as product_name,
            p.price,
            p.image_url,
            p.stock_quantity,
            (c.quantity * p.price) as subtotal
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();
    
    $cart_summary = getCartSummary($pdo, $user_id);
    
    echo json_encode([
        'success' => true,
        'cart_items' => $cart_items,
        'cart_summary' => $cart_summary
    ]);
}

function clearCart($pdo, $user_id) {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Cart cleared',
        'cart_summary' => [
            'item_count' => 0,
            'total_quantity' => 0,
            'total_amount' => 0
        ]
    ]);
}

function getCartSummary($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(c.id) as item_count,
            COALESCE(SUM(c.quantity), 0) as total_quantity,
            COALESCE(SUM(c.quantity * p.price), 0) as total_amount
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}
?> 