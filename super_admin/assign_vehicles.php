<?php
session_start();
require_once '../config/db.php';
require_once '../utils/auth_check.php';

$vendor_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'local';

if ($role !== 'super') {
    $permStmt = $pdo->prepare("SELECT can_assign_vehicle FROM delegations WHERE vendor_id = ?");
    $permStmt->execute([$vendor_id]);
    $permissions = $permStmt->fetch();

    if (!$permissions || $permissions['can_assign_vehicle'] != 1) {
        echo "<div style='padding:20px; color:red;'>❌ Access Denied: You can not assign vehicles.</div>";
        exit;
    }
}

$drivers = $pdo->query("SELECT id, name FROM drivers")->fetchAll();
// ✅ Fetch all vehicles (including assigned ones)
$vehicles = $pdo->query("
    SELECT v.id, v.registration_no, d.name AS driver_name 
    FROM vehicles v 
    LEFT JOIN drivers d ON v.assigned_driver_id = d.id
")->fetchAll();

$message = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $driver_id = $_POST['driver_id'] ?? null;
    $vehicle_id = $_POST['vehicle_id'] ?? null;

    if ($driver_id && $vehicle_id) {
        try {
            // ✅ Allow reassignment of vehicles
            $stmt = $pdo->prepare("UPDATE vehicles SET assigned_driver_id = ? WHERE id = ?");
            $stmt->execute([$driver_id, $vehicle_id]);
            $message = "✅ Vehicle assignment updated successfully.";
        } catch (PDOException $e) {
            $error = "❌ Error: " . $e->getMessage();
        }
    } else {
        $error = "❌ Please select both a driver and a vehicle.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign or Reassign Vehicles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h3>🚚 Assign / Reassign Vehicles to Drivers</h3>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="card p-4 shadow bg-white">
            <div class="mb-3">
                <label class="form-label">Driver</label>
                <select name="driver_id" class="form-select" required>
                    <option value="">-- Choose Driver --</option>
                    <?php foreach ($drivers as $d): ?>
                        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Vehicle</label>
                <select name="vehicle_id" class="form-select" required>
                    <option value="">-- Choose Vehicle --</option>
                    <?php foreach ($vehicles as $v): ?>
                        <option value="<?= $v['id'] ?>">
                            <?= htmlspecialchars($v['registration_no']) ?>
                            <?php if ($v['driver_name']): ?>
                                (Assigned to <?= htmlspecialchars($v['driver_name']) ?>)
                            <?php else: ?>
                                (Unassigned)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100">Assign / Reassign Vehicle</button>
        </form>

        <div class="mt-3 d-flex justify-content-between">
            <a href="../public/dashboard.php" class="btn btn-outline-secondary btn-sm">← Back to Dashboard</a>
            <a href="reporting_panel.php" class="btn btn-outline-secondary btn-sm">← Back to Report</a>
        </div>
    </div>
</body>
</html>
