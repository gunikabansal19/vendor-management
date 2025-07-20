<?php
session_start();
require_once '../config/db.php';
require_once '../utils/auth_check.php';

$vendor_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'local';
$canAddDriver = false;

if (!$vendor_id) {
    echo "Access denied. Please log in.";
    exit;
}

if ($role !== 'super') {
    $permStmt = $pdo->prepare("SELECT can_add_driver FROM delegations WHERE vendor_id = ?");
    $permStmt->execute([$vendor_id]);
    $perm = $permStmt->fetch();
    $canAddDriver = $perm && $perm['can_add_driver'] == 1;
} else {
    $canAddDriver = true; // Super vendor can always add drivers
}

if ($role === 'super') {
    $stmt = $pdo->query("
        SELECT d.id, d.name, d.license_number, d.license_expiry, d.vendor_id,
               v.registration_no AS vehicle_number, v.model AS vehicle_model,
               u.name AS vendor_name
        FROM drivers d
        LEFT JOIN vehicles v ON d.assigned_vehicle_id = v.id
        LEFT JOIN users u ON d.vendor_id = u.id AND u.type = 'sub_vendor'
    ");
    $drivers = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare("
        SELECT d.id, d.name, d.license_number, d.license_expiry, d.vendor_id,
               v.registration_no AS vehicle_number, v.model AS vehicle_model
        FROM drivers d
        LEFT JOIN vehicles v ON d.assigned_vehicle_id = v.id
        WHERE d.vendor_id = ?
    ");
    $stmt->execute([$vendor_id]);
    $drivers = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Driver List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-person-badge"></i> Driver List</h2>
        <div>
            <a href="../public/dashboard.php" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left-circle"></i> Back to Dashboard
            </a>
            <?php if ($canAddDriver): ?>
                <a href="add_driver.php" class="btn btn-success">+ Add Driver</a>
            <?php endif; ?>
        </div>
    </div>

    <table class="table table-bordered bg-white shadow">
        <thead class="table-primary">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>License #</th>
            <th>License Expiry</th>
            <th>Assigned Vehicle</th>
            <th>Vehicle Model</th>
            <?php if ($role === 'super'): ?>
                <th>Vendor Name</th>
                <th>Action</th>
            <?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php if (count($drivers) > 0): ?>
            <?php foreach ($drivers as $driver): ?>
                <tr>
                    <td><?= htmlspecialchars($driver['id']) ?></td>
                    <td><?= htmlspecialchars($driver['name']) ?></td>
                    <td><?= htmlspecialchars($driver['license_number']) ?></td>
                    <td><?= htmlspecialchars($driver['license_expiry']) ?></td>
                    <td><?= htmlspecialchars($driver['vehicle_number'] ?? 'Unassigned') ?></td>
                    <td><?= htmlspecialchars($driver['vehicle_model'] ?? '-') ?></td>
                    <?php if ($role === 'super'): ?>
                        <td><?= htmlspecialchars($driver['vendor_name'] ?? 'N/A') ?></td>
                        <td>
                            <a href="edit_driver.php?id=<?= $driver['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil-square"></i> Edit
                            </a>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="<?= $role === 'super' ? 8 : 6 ?>" class="text-center">No drivers found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
