<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'seller') {
    header("Location: ../login.php");
    exit();
}

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.seller_id = ? ORDER BY p.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Products</title>
    <style>
        body { background: #f0f2f5; font-family: Arial, sans-serif; }
        .container { max-width: 900px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        h1 { color: #a50c2a; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #a50c2a; color: #fff; }
        .btn { padding: 6px 14px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; background: #2196f3; color: white; }
        .btn:hover { background: #1769aa; }
    </style>
</head>
<body>
    <div class="container">
        <h1>My Products</h1>
        <table>
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Featured</th>
                <th>Action</th>
            </tr>
            <?php foreach ($products as $product): ?>
            <tr>
                <td><?= htmlspecialchars($product['name']) ?></td>
                <td><?= htmlspecialchars($product['category_name']) ?></td>
                <td>$<?= htmlspecialchars($product['price']) ?></td>
                <td><?= htmlspecialchars($product['stock_quantity']) ?></td>
                <td><?= $product['is_featured'] ? 'Yes' : 'No' ?></td>
                <td><a href="edit_product.php?id=<?= $product['id'] ?>" class="btn">Edit</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <div style="text-align:center; margin-top:15px;">
            <a href="dashboard.php">Back to Dashboard</a>
        </div>
    </div>
</body>
</html> 