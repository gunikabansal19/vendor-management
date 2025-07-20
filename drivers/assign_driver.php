<?php
session_start();
require_once '../config/db.php';
require_once '../utils/auth_check.php';

$vendor_id = $_SESSION['vendor_id'] ?? null;
$role = $_SESSION['role'] ?? 'local';
$message = "";
$error = "";

// Check permission for non-super vendors
if ($role !== 'super') {
    $permStmt = $pdo->prepare("SELECT can_add_driver FROM delegations WHERE vendor_id = ?");
    $permStmt->execute([$vendor_id]);
    $perm = $permStmt->fetch();

    if (!$perm || $perm['can_add_driver'] != 1) {
        echo "<div class='alert alert-danger p-4 m-5'>❌ Access Denied: You are not allowed to assign drivers to vehicles.</div>";
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $driver_id = $_POST['driver_id'] ?? null;
    $vehicle_id = $_POST['vehicle_id'] ?? null;

    if ($driver_id && $vehicle_id) {
        $stmt = $pdo->prepare("UPDATE drivers SET assigned_vehicle = ? WHERE id = ? AND vendor_id = ?");
        $stmt->execute([$vehicle_id, $driver_id, $vendor_id]);
        $message = " Driver assigned successfully!";
    } else {
        $error = "❌ Please select both a driver and a vehicle.";
    }
}

// Fetch dropdown options
if ($role === 'super') {
    $drivers = $pdo->query("SELECT id, name FROM drivers")->fetchAll();
    $vehicles = $pdo->query("SELECT id, number FROM vehicles")->fetchAll();
} else {
    $stmt1 = $pdo->prepare("SELECT id, name FROM drivers WHERE vendor_id = ?");
    $stmt1->execute([$vendor_id]);
    $drivers = $stmt1->fetchAll();

    $stmt2 = $pdo->prepare("SELECT id, number FROM vehicles WHERE vendor_id = ?");
    $stmt2->execute([$vendor_id]);
    $vehicles = $stmt2->fetchAll();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Driver to Vehicle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Assign Driver to Vehicle</h4>
        </div>
        <div class="card-body">

            <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php elseif ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Select Driver</label>
                    <select name="driver_id" class="form-select" required>
                        <option value="">-- Choose Driver --</option>
                        <?php foreach ($drivers as $driver): ?>
                            <option value="<?= $driver['id'] ?>"><?= htmlspecialchars($driver['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Select Vehicle</label>
                    <select name="vehicle_id" class="form-select" required>
                        <option value="">-- Choose Vehicle --</option>
                        <?php foreach ($vehicles as $vehicle): ?>
                            <option value="<?= $vehicle['id'] ?>"><?= htmlspecialchars($vehicle['number']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">Assign</button>
                <a href="driver_list.php" class="btn btn-secondary ms-2">Back to Driver List</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
