<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $business_name = trim($_POST['business_name']);
    $business_description = trim($_POST['business_description']);
    $contact_phone = trim($_POST['contact_phone']);
    $business_address = trim($_POST['business_address']);
    $business_license = trim($_POST['business_license']);

    $errors = [];
    if (empty($business_name) || empty($business_description) || empty($contact_phone) || empty($business_address)) {
        $errors[] = "All required fields must be filled.";
    }

    // Check if already applied
    $stmt = $pdo->prepare("SELECT id FROM seller_profiles WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    if ($stmt->fetch()) {
        $errors[] = "You have already submitted a seller application.";
    }

    if (empty($errors)) {
        // Insert seller profile
        $stmt = $pdo->prepare("INSERT INTO seller_profiles (user_id, business_name, business_description, contact_phone, business_address, business_license, is_approved) VALUES (?, ?, ?, ?, ?, ?, 0)");
        $stmt->execute([$_SESSION['user_id'], $business_name, $business_description, $contact_phone, $business_address, $business_license]);

        // Update user role to 'seller'
        $stmt = $pdo->prepare("UPDATE users SET role = 'seller' WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);

        $_SESSION['success'] = "Your seller application has been submitted!";
        header("Location: seller/pending_approval.php");
        exit();
    } else {
        $_SESSION['errors'] = $errors;
        header("Location: seller_register.php");
        exit();
    }
}
?> 