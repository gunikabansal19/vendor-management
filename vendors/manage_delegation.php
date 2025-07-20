<?php
require_once '../utils/auth_check.php';
require_once '../config/db.php';

$parentId = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role !== 'super') {
    echo "Access denied";
    exit;
}

// Get all sub-vendors
$stmt = $pdo->prepare("SELECT id, name FROM vendors WHERE parent_id = ?");
$stmt->execute([$parentId]);
$subVendors = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head><title>Delegate Access</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
<div class="container mt-5">
    <h3>Delegation Control Panel</h3>
    <?php foreach ($subVendors as $vendor): ?>
        <?php
        $stmt = $pdo->prepare("SELECT * FROM delegations WHERE vendor_id = ?");
        $stmt->execute([$vendor['id']]);
        $perm = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>
        <form method="POST" action="save_delegation.php" class="border p-3 mb-4">
            <h5><?= htmlspecialchars($vendor['name']) ?></h5>
            <input type="hidden" name="vendor_id" value="<?= $vendor['id'] ?>">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="can_add_driver" <?= $perm['can_add_driver'] ? 'checked' : '' ?>>
                <label class="form-check-label">Can Add Driver</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="can_add_vehicle" <?= $perm['can_add_vehicle'] ? 'checked' : '' ?>>
                <label class="form-check-label">Can Add Vehicle</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="can_upload_docs" <?= $perm['can_upload_docs'] ? 'checked' : '' ?>>
                <label class="form-check-label">Can Upload Documents</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="can_assign_vehicle" <?= $perm['can_assign_vehicle'] ? 'checked' : '' ?>>
                <label class="form-check-label">Can Assign Vehicles</label>
            </div>
            <button class="btn btn-primary mt-2" type="submit">Save</button>
        </form>
    <?php endforeach; ?>
</div>
</body>
</html>
