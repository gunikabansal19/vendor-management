<?php
session_start();
require_once '../config/db.php';
require_once '../utils/auth_check.php';

$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'local';

if (!$user_id) {
    header("Location: ../login.php");
    exit;
}

$canAddVehicle = false;

// Check permissions
if ($role === 'super') {
    $canAddVehicle = true;
} else {
    $permStmt = $pdo->prepare("SELECT can_add_vehicle FROM delegations WHERE vendor_id = ?");
    $permStmt->execute([$user_id]);
    $perm = $permStmt->fetch();
    $canAddVehicle = $perm && $perm['can_add_vehicle'] == 1;
}

// Fetch vehicles
if ($role === 'super') {
    $stmt = $pdo->query("
        SELECT v.*, ve.name AS vendor_name 
        FROM vehicles v
        LEFT JOIN users ve ON v.vendor_id = ve.id AND ve.type = 'sub_vendor'
    ");
    $vehicles = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE vendor_id = ?");
    $stmt->execute([$user_id]);
    $vehicles = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vehicle List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-truck"></i> Vehicle List</h2>
        <div>
            <a href="../public/dashboard.php" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left-circle"></i> Back to Dashboard
            </a>
            <?php if ($canAddVehicle): ?>
                <a href="add_vehicle.php" class="btn btn-success">+ Add Vehicle</a>
            <?php endif; ?>
        </div>
    </div>
    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Registration No</th>
                <th>Fuel Type</th>
                <th>Model</th>
                <?php if ($role === 'super'): ?>
                    <th>Vendor Name</th>
                <?php endif; ?>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($vehicles): ?>
                <?php foreach ($vehicles as $vehicle): ?>
                    <tr>
                        <td><?= htmlspecialchars($vehicle['id']) ?></td>
                        <td><?= htmlspecialchars($vehicle['registration_no']) ?></td>
                        <td><?= htmlspecialchars($vehicle['fuel_type']) ?></td>
                        <td><?= htmlspecialchars($vehicle['model']) ?></td>
                        <?php if ($role === 'super'): ?>
                            <td><?= htmlspecialchars($vehicle['vendor_name']) ?></td>
                        <?php endif; ?>
                        <td><?= htmlspecialchars($vehicle['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="<?= $role === 'super' ? 6 : 5 ?>" class="text-center">No vehicles found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
