<?php
session_start();
require_once '../config/db.php';
require_once '../utils/auth_check.php';

//  Only super_vendor can access
if ($_SESSION['role'] !== 'super') {
    die("❌ Access denied");
}

//  Fetch sub-vendor accounts
$stmt = $pdo->query("SELECT id, name, email, status FROM users WHERE type = 'sub_vendor'");
$vendors = $stmt->fetchAll();

//  Get drivers and vehicles per vendor
$stats = [];
foreach ($vendors as $vendor) {
    $driverStmt = $pdo->prepare("SELECT COUNT(*) FROM drivers WHERE vendor_id = ?");
    $driverStmt->execute([$vendor['id']]);
    $vehicleStmt = $pdo->prepare("SELECT COUNT(*) FROM vehicles WHERE vendor_id = ?");
    $vehicleStmt->execute([$vendor['id']]);
    $stats[$vendor['id']] = [
        'drivers' => $driverStmt->fetchColumn(),
        'vehicles' => $vehicleStmt->fetchColumn(),
    ];
}

//  Export CSV (without last_login)
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="vendor_report.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Vendor Name', 'Email', 'Status', 'Total Drivers', 'Total Vehicles']);

    foreach ($vendors as $v) {
        fputcsv($output, [
            $v['name'],
            $v['email'],
            ucfirst($v['status']),
            $stats[$v['id']]['drivers'],
            $stats[$v['id']]['vehicles'],
        ]);
    }
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reporting Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary">📊 Sub-Vendor Report</h3>
        <a href="?export=csv" class="btn btn-outline-success">⬇️ Export CSV</a>
    </div>

    <table class="table table-bordered table-hover bg-white shadow-sm">
        <thead class="table-info text-center">
            <tr>
                <th>Vendor Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Total Drivers</th>
                <th>Total Vehicles</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($vendors as $vendor): ?>
            <tr>
                <td><?= htmlspecialchars($vendor['name']) ?></td>
                <td><?= htmlspecialchars($vendor['email']) ?></td>
                <td class="text-center"><?= ucfirst($vendor['status']) ?></td>
                <td class="text-center"><?= $stats[$vendor['id']]['drivers'] ?></td>
                <td class="text-center"><?= $stats[$vendor['id']]['vehicles'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- 🔙 Back to Dashboard Button -->
    <div class="mt-3">
        <a href="../public/dashboard.php" class="btn btn-outline-secondary btn-sm">← Back to Dashboard</a>
    </div>
</div>
</body>
</html>
