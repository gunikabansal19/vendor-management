<?php
session_start();
require_once '../config/db.php';
require_once '../utils/auth_check.php';

// ✅ Only super_vendor can access
if ($_SESSION['role'] !== 'super') die("Access denied");

$id = $_GET['id'] ?? null;
if (!$id) die("Invalid ID");

// ✅ Ensure user exists and is a sub-vendor
$check = $pdo->prepare("SELECT * FROM users WHERE id = ? AND type = 'sub_vendor'");
$check->execute([$id]);
$user = $check->fetch();
if (!$user) die("Invalid user");

// ✅ Fetch existing rights if any
$existing = $pdo->prepare("SELECT * FROM delegations WHERE vendor_id = ?");
$existing->execute([$id]);
$rights = $existing->fetch();

// ✅ On form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $can_add_driver = isset($_POST['can_add_driver']) ? 1 : 0;
    $can_add_vehicle = isset($_POST['can_add_vehicle']) ? 1 : 0;
    $can_upload_docs = isset($_POST['can_upload_docs']) ? 1 : 0;
    $can_assign_vehicle = isset($_POST['can_assign_vehicle']) ? 1 : 0;

    if ($rights) {
        // Update rights
        $stmt = $pdo->prepare("
            UPDATE delegations SET 
                can_add_driver = ?, 
                can_add_vehicle = ?, 
                can_upload_docs = ?, 
                can_assign_vehicle = ?
            WHERE vendor_id = ?
        ");
        $stmt->execute([
            $can_add_driver, $can_add_vehicle, $can_upload_docs, $can_assign_vehicle, $id
        ]);
    } else {
        // Insert new rights
        $stmt = $pdo->prepare("
            INSERT INTO delegations (vendor_id, can_add_driver, can_add_vehicle, can_upload_docs, can_assign_vehicle) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $id, $can_add_driver, $can_add_vehicle, $can_upload_docs, $can_assign_vehicle
        ]);
    }

    header("Location: manage_vendors.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delegate Rights</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h3 class="mb-4">Delegate Rights to <?= htmlspecialchars($user['name']) ?></h3>
    <form method="POST" class="card p-4 shadow-sm bg-white">
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="can_add_driver" <?= !empty($rights['can_add_driver']) ? 'checked' : '' ?>>
            <label class="form-check-label">Can Add Drivers</label>
        </div>
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="can_add_vehicle" <?= !empty($rights['can_add_vehicle']) ? 'checked' : '' ?>>
            <label class="form-check-label">Can Add Vehicles</label>
        </div>
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="can_upload_docs" <?= !empty($rights['can_upload_docs']) ? 'checked' : '' ?>>
            <label class="form-check-label">Can Upload Documents</label>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="can_assign_vehicle" <?= !empty($rights['can_assign_vehicle']) ? 'checked' : '' ?>>
            <label class="form-check-label">Can Assign Vehicle</label>
        </div>
        <button class="btn btn-success w-100">💾 Save Rights</button>
    </form>
    <div class="mt-3">
        <a href="manage_vendors.php" class="btn btn-outline-secondary btn-sm">← Back to Vendor List</a>
    </div>
</div>
</body>
</html>
