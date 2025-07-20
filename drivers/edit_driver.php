<?php
session_start();
require_once '../config/db.php';
require_once '../utils/auth_check.php';

$driverId = $_GET['id'] ?? null;
$vendor_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'local';
$message = $error = '';

if (!$driverId) {
    echo "❌ Driver ID is missing.";
    exit;
}

// Fetch driver details
$stmt = $pdo->prepare("SELECT * FROM drivers WHERE id = ?");
$stmt->execute([$driverId]);
$driver = $stmt->fetch();

if (!$driver) {
    echo "❌ Driver not found.";
    exit;
}

// Authorization: Super can edit anyone. Sub vendors can edit only their own drivers.
if ($role !== 'super' && $driver['vendor_id'] != $vendor_id) {
    echo "❌ Access denied.";
    exit;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $license_number = trim($_POST['license_number']);
    $license_expiry = $_POST['license_expiry'];
    $new_vendor_id = $role === 'super' ? $_POST['vendor_id'] : $vendor_id;

    try {
        $updateStmt = $pdo->prepare("
            UPDATE drivers 
            SET name = ?, license_number = ?, license_expiry = ?, vendor_id = ? 
            WHERE id = ?
        ");
        $updateStmt->execute([$name, $license_number, $license_expiry, $new_vendor_id, $driverId]);

        $message = "✅ Driver updated successfully.";

        // Refresh driver data
        $stmt->execute([$driverId]);
        $driver = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "❌ Error: " . $e->getMessage();
    }
}

// Get vendor list if super admin
$vendors = [];
if ($role === 'super') {
    $vendors = $pdo->query("SELECT id, name FROM users WHERE type = 'sub_vendor'")->fetchAll();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Driver</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-pencil-square"></i> Edit Driver</h2>
        <a href="driver_list.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left-circle"></i> Back to Driver List
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="bg-white p-4 shadow-sm rounded">
        <div class="mb-3">
            <label class="form-label">Name:</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($driver['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">License Number:</label>
            <input type="text" name="license_number" class="form-control" value="<?= htmlspecialchars($driver['license_number']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">License Expiry:</label>
            <input type="date" name="license_expiry" class="form-control" value="<?= htmlspecialchars($driver['license_expiry']) ?>" required>
        </div>

        <?php if ($role === 'super'): ?>
            <div class="mb-3">
                <label class="form-label">Vendor:</label>
                <select name="vendor_id" class="form-select" required>
                    <option value="">-- Select Vendor --</option>
                    <?php foreach ($vendors as $vendor): ?>
                        <option value="<?= $vendor['id'] ?>" <?= $vendor['id'] == $driver['vendor_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($vendor['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save2"></i> Save Changes
        </button>
    </form>
</div>

</body>
</html>
