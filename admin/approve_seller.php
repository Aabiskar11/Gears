<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $seller_id = (int)$_GET['id'];
    // Approve the seller
    $stmt = $pdo->prepare("UPDATE seller_profiles SET is_approved = 1, approved_by = ?, approved_at = NOW() WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id'], $seller_id]);
}

header("Location: manage_sellers.php");
exit();
?> 