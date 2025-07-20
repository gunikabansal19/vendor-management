<?php
session_start();
require_once '../utils/auth_check.php';
require_once '../config/db.php';

$user_id = $_SESSION['user_id'] ?? null;
$vendor_id = $_SESSION['vendor_id'] ?? null;
$role = $_SESSION['role'] ?? 'local';
$type = $_SESSION['type'] ?? 'sub_vendor';

if (!$user_id || !$vendor_id) {
    header("Location: ../auth/login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT name FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$vendorName = $user['name'] ?? 'Vendor';

$permissions = [];
if ($type === 'sub_vendor') {
    $permStmt = $pdo->prepare("SELECT * FROM delegations WHERE vendor_id = ?");
    $permStmt->execute([$user_id]);
    $permissions = $permStmt->fetch() ?: [
        'can_add_vehicle' => 0,
        'can_assign_vehicle' => 0,
        'can_add_driver' => 0,
        'can_upload_docs' => 0
    ];
}

if ($type === 'super_vendor') {
    $countVendors = $pdo->query("SELECT COUNT(*) FROM users WHERE type = 'sub_vendor'")->fetchColumn();
    $countVehicles = $pdo->query("SELECT COUNT(*) FROM vehicles")->fetchColumn();
    $countDrivers = $pdo->query("SELECT COUNT(*) FROM drivers")->fetchColumn();
    $stmtCountDocs = $pdo->query("SELECT COUNT(*) FROM drivers as d inner join driver_docs as dd on dd.driver_id = d.id");
    $countDocs = $stmtCountDocs->fetchColumn();

    $stmt = $pdo->prepare("SELECT name, email, role, created_at FROM users WHERE type = 'sub_vendor'");
    $stmt->execute();
    $subVendors = $stmt->fetchAll();
    $permissions = [
        'can_add_vehicle' => 1,
        'can_assign_vehicle' => 1,
        'can_add_driver' => 1,
        'can_upload_docs' => 1
    ];
} else {
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM users WHERE type = 'sub_vendor' and vendor_id = ?");$stmtCount->execute([$user_id]); 
    $countVendors = $stmtCount->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM vehicles WHERE vendor_id = ?");
    $stmt->execute([$user_id]);
    $countVehicles = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM drivers WHERE vendor_id = ?");
    $stmt->execute([$user_id]);
    $countDrivers = $stmt->fetchColumn();

   $stmtCountDocs = $pdo->prepare("SELECT COUNT(*) FROM drivers as d inner join driver_docs as dd on dd.driver_id = d.id where d.vendor_id = ?");
    $stmtCountDocs->execute([$user_id]);
    $countDocs = $stmtCountDocs->fetchColumn();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vendor Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f2f5; }
        .card-box {
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease-in-out;
        }
        .card-box:hover {
            transform: translateY(-8px);
        }
        .navbar-brand {
            font-weight: 600;
            font-size: 1.6rem;
        }
        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
        }
        .dashboard-header {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
        }
        .btn-outline-dark {
            font-weight: 500;
        }
        table th, table td {
            vertical-align: middle !important;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
    <a class="navbar-brand" href="#">Vendor Dashboard</a>
    <div class="ms-auto">
        <span class="text-white me-3">Welcome, <strong><?= htmlspecialchars($vendorName) ?></strong></span>
        <a href="../auth/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="dashboard-header mb-4">Dashboard Overview</h2>
    <div class="row g-4">
        <?php if ($role !== 'local'): ?>
            <div class="col-md-3">
                <div class="card card-box text-center bg-white">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-people-fill"></i> Sub-Vendors</h5>
                        <h3 class="text-primary"><?= $countVendors ?></h3>
                        <a href="../vendors/manage_vendors.php" class="btn btn-sm btn-outline-primary mt-2">Manage</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="col-md-3">
            <div class="card card-box text-center bg-white">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-truck"></i> Vehicles</h5>
                    <h3 class="text-success"><?= $countVehicles ?></h3>
                    <a href="../fleet/vehicle_list.php" class="btn btn-sm btn-outline-success mt-2">View Fleet</a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-box text-center bg-white">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-person-badge"></i> Drivers</h5>
                    <h3 class="text-info"><?= $countDrivers ?></h3>
                    <a href="../drivers/driver_list.php" class="btn btn-sm btn-outline-info mt-2">View Drivers</a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-box text-center bg-white">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-file-earmark-text"></i> Documents</h5>
                    <h3 class="text-danger"><?= $countDocs ?></h3>
                    <a href="../drivers/view_docs.php" class="btn btn-sm btn-outline-danger mt-2">View</a>
                </div>
            </div>
        </div>
    </div>

    <?php if ($permissions['can_assign_vehicle'] == 1): ?>
        <div class="row mt-5 g-4">
            <div class="col-md-6">
                <div class="card card-box">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-link-45deg"></i> Assign Vehicles</h5>
                        <p class="text-muted">Assign vehicles to drivers across vendors.</p>
                        <a href="../super_admin/assign_vehicles.php" class="btn btn-outline-dark">Go to Assign</a>
                    </div>
                </div>
            </div>

    <?php endif; ?>
    <?php if ($type ==='super_vendor'): ?>
            <div class="col-md-6">
                <div class="card card-box">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-graph-up"></i> Reporting Panel</h5>
                        <p class="text-muted">Monitor drivers, vehicles, vendor status, and export reports.</p>
                        <a href="../super_admin/reporting_panel.php" class="btn btn-outline-dark">Open Report</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5">
            <h5 class="mb-3">Sub-Vendor Hierarchy</h5>
            <div class="table-responsive">
                <table class="table table-bordered bg-white">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($subVendors)): ?>
                            <tr><td colspan="4" class="text-center text-muted">No sub-vendors found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($subVendors as $v): ?>
                                <tr>
                                  <td><?= htmlspecialchars($v['name']) ?></td>
                                    <td><?= htmlspecialchars($v['email']) ?></td>
                                    <td><?= ucfirst($v['role']) ?></td>
                                    <td><?= $v['created_at'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
