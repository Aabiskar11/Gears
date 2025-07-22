<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch all sellers and their approval status
$stmt = $pdo->query("SELECT u.id, u.fullname, u.email, sp.business_name, sp.is_approved, sp.created_at FROM users u JOIN seller_profiles sp ON u.id = sp.user_id ORDER BY sp.is_approved ASC, sp.created_at DESC");
$sellers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Sellers</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #a50c2a; color: white; }
        .btn { padding: 6px 14px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; }
        .btn-approve { background: #4caf50; color: white; }
        .btn-reject { background: #f44336; color: white; }
        .btn-view { background: #2196f3; color: white; }
        .approved { color: #388e3c; font-weight: bold; }
        .pending { color: #f57c00; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Manage Sellers</h1>
    <table>
        <tr>
            <th>Full Name</th>
            <th>Email</th>
            <th>Business Name</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php foreach ($sellers as $seller): ?>
        <tr>
            <td><?= htmlspecialchars($seller['fullname']) ?></td>
            <td><?= htmlspecialchars($seller['email']) ?></td>
            <td><?= htmlspecialchars($seller['business_name']) ?></td>
            <td>
                <?php if ($seller['is_approved']): ?>
                    <span class="approved">Approved</span>
                <?php else: ?>
                    <span class="pending">Pending</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if (!$seller['is_approved']): ?>
                    <a href="approve_seller.php?id=<?= $seller['id'] ?>" class="btn btn-approve">Approve</a>
                    <a href="reject_seller.php?id=<?= $seller['id'] ?>" class="btn btn-reject">Reject</a>
                <?php else: ?>
                    <span>-</span>
                <?php endif; ?>
                <a href="view_seller.php?id=<?= $seller['id'] ?>" class="btn btn-view">View Details</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <p><a href="dashboard.php">Back to Dashboard</a></p>
</body>
</html> 