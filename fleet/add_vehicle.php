<?php
session_start();
require_once '../config/db.php';
require_once '../utils/auth_check.php';

$vendor_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'local';
$message = $error = "";


// ✅ Super Vendor can skip delegation check
if ($role !== 'super') {
    $permStmt = $pdo->prepare("SELECT can_add_vehicle FROM delegations WHERE vendor_id = ?");
    $permStmt->execute([$vendor_id]);
    $permissions = $permStmt->fetch();

    if (!$permissions || $permissions['can_add_vehicle'] != 1) {
        echo "<div style='padding:20px; color:red;'>❌ Access Denied: You can not add vehicles.</div>";
        exit;
    }
}

$stmtvendors = $pdo->prepare("SELECT id, name FROM users where type = 'sub_vendor' and vendor_id = ?");
$stmtvendors->execute([$vendor_id]);
$vendors = $stmtvendors->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $registration_no = trim($_POST['registration_no']);
    $model = trim($_POST['model']);
    $fuel_type = trim($_POST['fuel_type']);
    $seating_capacity = (int) $_POST['seating_capacity'];
    $selected_vendor = (int) $_POST['selected_vendor'];

    if ($registration_no && $model && $fuel_type && $seating_capacity && $selected_vendor) {
        try {
            $stmt = $pdo->prepare("INSERT INTO vehicles (vendor_id, registration_no, model, fuel_type, seating_capacity, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$selected_vendor, $registration_no, $model, $fuel_type, $seating_capacity]);
            $message = "✅ Vehicle added successfully.";
        } catch (PDOException $e) {
            $error = "❌ Error: " . $e->getMessage();
        }
    } else {
        $error = "❌ All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Vehicle (Admin Only)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Add New Vehicle</h5>
        </div>
        <div class="card-body">

            <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php elseif ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Select Vendor</label>
                    <select name="selected_vendor" class="form-select" required>
                        <option value="">-- Choose Vendor --</option>
                        <?php foreach ($vendors as $v): ?>
                            <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Registration Number</label>
                    <input type="text" name="registration_no" class="form-control" required placeholder="e.g. PB10AB1234">
                </div>

                <div class="mb-3">
                    <label class="form-label">Model</label>
                    <input type="text" name="model" class="form-control" required placeholder="e.g. Tata Ace">
                </div>

                <div class="mb-3">
                    <label class="form-label">Fuel Type</label>
                    <input type="text" name="fuel_type" class="form-control" required placeholder="e.g. Diesel, Petrol, CNG">
                </div>

                <div class="mb-3">
                    <label class="form-label">Seating Capacity</label>
                    <input type="number" name="seating_capacity" class="form-control" required placeholder="e.g. 4">
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">Add Vehicle</button>
                    <a href="vehicle_list.php" class="btn btn-secondary">Back to List</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
