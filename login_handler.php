<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $errors = [];
    
    // Validation
    if (empty($email)) {
        $errors[] = "Email is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If no validation errors, attempt login
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id, fullname, email, password, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['fullname'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['logged_in'] = true;
                
                // Redirect based on user role
                switch ($user['role']) {
                    case 'admin':
                        header("Location: admin/dashboard.php");
                        break;
                    case 'seller':
                        // Check if seller is approved
                        $stmt = $pdo->prepare("SELECT is_approved FROM seller_profiles WHERE user_id = ?");
                        $stmt->execute([$user['id']]);
                        $profile = $stmt->fetch();
                        
                        if ($profile && $profile['is_approved']) {
                            header("Location: seller/dashboard.php");
                        } else {
                            header("Location: seller/pending_approval.php");
                        }
                        break;
                    default: // customer
                        header("Location: landingpage.php");
                        break;
                }
                exit();
            } else {
                $errors[] = "Invalid email or password";
            }
        } catch (PDOException $e) {
            $errors[] = "Login failed. Please try again.";
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: login.php");
        exit();
    }
}
?> 