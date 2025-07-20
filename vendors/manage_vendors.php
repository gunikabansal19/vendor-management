<?php
session_start();
require_once '../config/db.php';
require_once '../utils/auth_check.php';

$role = $_SESSION['role'] ?? '';
$parentId = $_SESSION['user_id'] ?? null;

//  Restrict access to super_vendor
if ($role == 'local') {
    echo "<div style='padding: 20px; color: red;'>❌ Access Denied: You can not manage sub-vendors.</div>";
    exit;
}

//  Fetch sub-vendors
if ($role !== 'super') {
    $stmt = $pdo->prepare("SELECT id, name, email, role, created_at, status FROM users WHERE vendor_id = ? and type = 'sub_vendor'");
    $stmt->execute([$parentId]);
    $subVendors = $stmt->fetchAll();
} else{
    $stmt2 = $pdo->query("SELECT id, name, email, role, created_at, status FROM users WHERE type = 'sub_vendor'");
    $subVendors = $stmt2->fetchAll();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Sub-Vendors</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 1000px; }
        .table th, .table td { vertical-align: middle; }
        .card { border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .header-title { font-weight: bold; font-size: 1.8rem; }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="header-title text-primary">👥 Sub-Vendor Management</div>
        <div>
            <a href="add_sub_vendor.php" class="btn btn-success me-2">+ Add Sub-Vendor</a>
            <a href="../public/dashboard.php" class="btn btn-outline-secondary">← Dashboard</a>
        </div>
    </div>

    <div class="card bg-white">
        <div class="card-body">
            <h5 class="card-title text-secondary mb-3">Registered Sub-Vendors</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-primary text-center">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($subVendors)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No sub-vendors found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($subVendors as $vendor): ?>
                                <tr>
                                    <td class="text-center"><?= $vendor['id'] ?></td>
                                    <td><?= htmlspecialchars($vendor['name']) ?></td>
                                    <td><?= htmlspecialchars($vendor['email']) ?></td>
                                    <td class="text-center text-capitalize">
                                        <?= htmlspecialchars($vendor['role']) ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="edit_sub_vendor.php?id=<?= $vendor['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="delegate_rights.php?id=<?= $vendor['id'] ?>" class="btn btn-sm btn-info">Rights</a>
                                        <a href="toggle_vendor_status.php?id=<?= $vendor['id'] ?>" class="btn btn-sm btn-secondary">
                                            <?= $vendor['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
