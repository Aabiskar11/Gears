<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'seller') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "Product ID missing.";
    exit();
}
$product_id = (int)$_GET['id'];

// Fetch product and check ownership
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND seller_id = ?");
$stmt->execute([$product_id, $_SESSION['user_id']]);
$product = $stmt->fetch();
if (!$product) {
    echo "Product not found or you do not have permission to edit this product.";
    exit();
}

// Get categories
$cat_stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$categories = $cat_stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock_quantity = (int)$_POST['stock_quantity'];
    $category_id = (int)$_POST['category_id'];
    $image_url = trim($_POST['image_url']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, stock_quantity=?, category_id=?, image_url=?, is_featured=? WHERE id=? AND seller_id=?");
    $stmt->execute([$name, $description, $price, $stock_quantity, $category_id, $image_url, $is_featured, $product_id, $_SESSION['user_id']]);
    $_SESSION['success'] = "Product updated!";
    header("Location: my_products.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <style>
        body { background: #f0f2f5; font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        h1 { color: #a50c2a; text-align: center; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #555; font-weight: 500; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; }
        .form-group textarea { resize: vertical; }
        .btn { width: 100%; padding: 12px; background: #a50c2a; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
        .btn:hover { background: #8b0a24; }
        .success { color: #388e3c; font-size: 14px; margin-bottom: 15px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Product</h1>
        <form method="POST">
            <div class="form-group">
                <label for="name">Product Name *</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="price">Price *</label>
                <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($product['price']); ?>" required>
            </div>
            <div class="form-group">
                <label for="stock_quantity">Stock Quantity *</label>
                <input type="number" id="stock_quantity" name="stock_quantity" min="0" value="<?php echo htmlspecialchars($product['stock_quantity']); ?>" required>
            </div>
            <div class="form-group">
                <label for="category_id">Category *</label>
                <select id="category_id" name="category_id" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php if ($cat['id'] == $product['category_id']) echo 'selected'; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="image_url">Image URL</label>
                <input type="url" id="image_url" name="image_url" value="<?php echo htmlspecialchars($product['image_url']); ?>">
            </div>
            <div class="form-group">
                <input type="checkbox" id="is_featured" name="is_featured" value="1" <?php if ($product['is_featured']) echo 'checked'; ?>>
                <label for="is_featured">Feature this product</label>
            </div>
            <button type="submit" class="btn">Save Changes</button>
        </form>
        <div style="text-align:center; margin-top:15px;">
            <a href="my_products.php">Back to My Products</a>
        </div>
    </div>
</body>
</html> 