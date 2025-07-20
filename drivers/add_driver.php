<?php
session_start();
require_once '../config/db.php';
require_once '../utils/auth_check.php';

$role = $_SESSION['role'] ?? 'local';
$vendor_id = $_SESSION['user_id'] ?? null;
$message = $error = "";

// Check permission for non-super vendors
if ($role !== 'super') {
    $permStmt = $pdo->prepare("SELECT can_add_driver FROM delegations WHERE vendor_id = ?");
    $permStmt->execute([$vendor_id]);
    $perm = $permStmt->fetch();

    if (!$perm || $perm['can_add_driver'] != 1) {
        echo "<div style='padding: 20px; color: red;'>❌ Access Denied: You are not allowed to add drivers.</div>";
        exit;
    }
}

$stmtVendors = $pdo->prepare("SELECT id, name FROM users where type = 'sub_vendor' and vendor_id = ?");
$stmtVendors->execute([$vendor_id]);
$subVendors = $stmtVendors->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $license_number = trim($_POST['license_number']);
    $license_expiry = $_POST['license_expiry'];
    $selected_vendor_id = (int) $_POST['selected_vendor'];

    if ($name && $license_number && $license_expiry && $selected_vendor_id) {
        try {
            $stmt = $pdo->prepare("INSERT INTO drivers (vendor_id, name, license_number, license_expiry) VALUES (?, ?, ?, ?)");
            $stmt->execute([$selected_vendor_id, $name, $license_number, $license_expiry]);
            $message = " Driver added successfully.";
        } catch (PDOException $e) {
            $error = "❌ Error: " . $e->getMessage();
        }
    } else {
        $error = "❌ Please fill all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Driver (Admin Only)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0">Add New Driver</h4>
        </div>
        <div class="card-body">

            <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php elseif ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <div class="mb-3">
                    <label class="form-label">Assign to Vendor</label>
                    <select name="selected_vendor" class="form-select" required>
                        <option value="">-- Choose Vendor --</option>
                        <?php foreach ($subVendors as $vendor): ?>
                            <option value="<?= $vendor['id'] ?>"><?= htmlspecialchars($vendor['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Driver Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="Enter driver's name">
                </div>

                <div class="mb-3">
                    <label class="form-label">License Number</label>
                    <input type="text" name="license_number" class="form-control" required placeholder="Enter license number">
                </div>

                <div class="mb-3">
                    <label class="form-label">License Expiry Date</label>
                    <input type="date" name="license_expiry" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success w-100">Add Driver</button>
            </form>
        </div>
    </div>

    <div class="mt-3 text-center">
        <a href="driver_list.php" class="btn btn-outline-secondary btn-sm">← Back to Driver List</a>
    </div>
</div>
</body>
</html>
