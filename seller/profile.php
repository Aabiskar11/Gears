<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'seller') {
    header("Location: ../login.php");
    exit();
}

// Get seller profile
$stmt = $pdo->prepare("SELECT * FROM seller_profiles WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch();

if (!$profile) {
    echo "Profile not found.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $business_name = trim($_POST['business_name']);
    $business_description = trim($_POST['business_description']);
    $contact_phone = trim($_POST['contact_phone']);
    $business_address = trim($_POST['business_address']);
    $business_license = trim($_POST['business_license']);

    $stmt = $pdo->prepare("UPDATE seller_profiles SET business_name=?, business_description=?, contact_phone=?, business_address=?, business_license=? WHERE user_id=?");
    $stmt->execute([$business_name, $business_description, $contact_phone, $business_address, $business_license, $_SESSION['user_id']]);
    $_SESSION['success'] = "Profile updated!";
    header("Location: profile.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Seller Profile</title>
    <style>
        body { background: #f0f2f5; font-family: Arial, sans-serif; }
        .container { max-width: 500px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        h1 { color: #a50c2a; text-align: center; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #555; font-weight: 500; }
        .form-group input, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; }
        .form-group textarea { resize: vertical; }
        .btn { width: 100%; padding: 12px; background: #a50c2a; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
        .btn:hover { background: #8b0a24; }
        .success { color: #388e3c; font-size: 14px; margin-bottom: 15px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Business Profile</h1>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="business_name">Business Name *</label>
                <input type="text" id="business_name" name="business_name" value="<?php echo htmlspecialchars($profile['business_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="business_description">Business Description *</label>
                <textarea id="business_description" name="business_description" rows="4" required><?php echo htmlspecialchars($profile['business_description']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="contact_phone">Contact Phone *</label>
                <input type="text" id="contact_phone" name="contact_phone" value="<?php echo htmlspecialchars($profile['contact_phone']); ?>" required>
            </div>
            <div class="form-group">
                <label for="business_address">Business Address *</label>
                <textarea id="business_address" name="business_address" rows="3" required><?php echo htmlspecialchars($profile['business_address']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="business_license">Business License</label>
                <input type="text" id="business_license" name="business_license" value="<?php echo htmlspecialchars($profile['business_license']); ?>">
            </div>
            <button type="submit" class="btn">Save Changes</button>
        </form>
        <div style="text-align:center; margin-top:15px;">
            <a href="dashboard.php">Back to Dashboard</a>
        </div>
    </div>
</body>
</html> 