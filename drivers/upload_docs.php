<?php
// File: drivers/upload_docs.php
session_start();
require_once '../config/db.php';
require_once '../utils/auth_check.php';

$role = $_SESSION['role'] ?? '';
$type = $_SESSION['type'] ?? '';
$user_id = $_SESSION['user_id'] ?? null;
$vendor_id = $_SESSION['vendor_id'] ?? null;

if (!$user_id || !$vendor_id) {
    echo "<div style='padding:20px;color:red;'>Session error: Please login again.</div>";
    exit;
}

$error = $message = "";

//  Super vendor sees all drivers
if ($type === 'super_vendor') {
    $stmt = $pdo->query("SELECT drivers.id, drivers.name, u.name AS vendor_name FROM drivers JOIN users as u ON drivers.vendor_id = u.id");
    $drivers = $stmt->fetchAll();

//  Sub-vendor with delegation rights
} elseif ($type === 'sub_vendor') {
    $permStmt = $pdo->prepare("SELECT can_upload_docs FROM delegations WHERE vendor_id = ?");
    $permStmt->execute([$user_id]);
    $permissions = $permStmt->fetch();

    if (!$permissions || intval($permissions['can_upload_docs']) !== 1) {
        echo "<div style='padding:20px;color:red;'>❌ Access Denied: You are not allowed to upload driver documents.</div>";
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, name FROM drivers WHERE vendor_id = ?");
    $stmt->execute([$vendor_id]);
    $drivers = $stmt->fetchAll();

// ❌ All other roles
} else {
    echo "<div style='padding:20px;color:red;'>❌ Access Denied: Invalid role.</div>";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload Driver Documents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
        <div class="container mt-4 d-flex justify-content-end">
        <a href="view_docs.php" class="btn btn-outline-secondary btn-sm">← Back to View Docs</a>
    </div>
<div class="container mt-5">
    <h2>📁 Upload Driver Documents</h2>
    <form method="POST" action="upload_docs_process.php" enctype="multipart/form-data" class="shadow p-4 bg-white">
        <div class="mb-3">
            <label class="form-label">👤 Driver</label>
            <select name="driver_id" class="form-select" required>
                <option value="">-- Select Driver --</option>
                <?php foreach ($drivers as $driver): ?>
                    <option value="<?= $driver['id'] ?>">
                        <?= htmlspecialchars($driver['name']) ?>
                        <?php if ($role === 'super') echo " ({$driver['vendor_name']})"; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">📄 Document Type</label>
            <select name="doc_type" class="form-select" required>
                <option value="">-- Select Type --</option>
                <option value="DL">Driving License</option>
                <option value="RC">Registration Certificate</option>
                <option value="Permit">Permit</option>
                <option value="Pollution">Pollution Certificate</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">📄 Upload File</label>
            <input type="file" name="document" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">🗓️ Expiry Date</label>
            <input type="date" name="expiry_date" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success"> Upload</button>
    </form>
</div>
</body>
</html>
