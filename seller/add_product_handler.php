<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'seller') {
    header("Location: ../login.php");
    exit();
}

// Check if seller is approved
$stmt = $pdo->prepare("SELECT is_approved FROM seller_profiles WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch();

if (!$profile || !$profile['is_approved']) {
    header("Location: pending_approval.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock_quantity = (int)$_POST['stock_quantity'];
    $category_id = (int)$_POST['category_id'];
    $image_url = trim($_POST['image_url']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    $errors = [];
    
    // Validation
    if (empty($name)) {
        $errors[] = "Product name is required";
    }
    
    if (empty($description)) {
        $errors[] = "Product description is required";
    }
    
    if ($price <= 0) {
        $errors[] = "Price must be greater than 0";
    }
    
    if ($stock_quantity < 0) {
        $errors[] = "Stock quantity cannot be negative";
    }
    
    if ($category_id <= 0) {
        $errors[] = "Please select a valid category";
    }
    
    // If no errors, add the product
    if (empty($errors)) {
        try {
            // Use placeholder image if no URL provided
            if (empty($image_url)) {
                $image_url = 'https://via.placeholder.com/250x200';
            }
            
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category_id, seller_id, image_url, stock_quantity, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $category_id, $_SESSION['user_id'], $image_url, $stock_quantity, $is_featured]);
            
            $_SESSION['success'] = "Product added successfully!";
            header("Location: dashboard.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Failed to add product. Please try again.";
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: add_product.php");
        exit();
    }
}

// If not POST request, redirect to add product page
header("Location: add_product.php");
exit();
?> 