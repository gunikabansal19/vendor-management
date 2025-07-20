<?php
session_start();
require_once '../config/db.php';
require_once '../utils/auth_check.php';

$role = $_SESSION['role'] ?? '';
$parentId = $_SESSION['user_id'] ?? null;
$id = $_GET['id'] ?? null;
$vendor = null;

if (!$id) {
    echo "<div style='padding: 20px; color: red;'>❌ Invalid request. Vendor ID missing.</div>";
    exit;
}

if ($role === 'super') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $vendor = $stmt->fetch();
} else {
    $permstmt = $pdo->prepare("SELECT * FROM users WHERE id = ? and vendor_id = ? AND type = 'sub_vendor'");
    $permstmt->execute([$id, $parentId]);
    $vendor = $permstmt->fetch();
    if (!$vendor) {
        echo "<div style='padding: 20px; color: red;'>❌ Vendor not found or unauthorized access</div>";
        exit;
    }
}

if (!$vendor) {
    echo "<div style='padding: 20px; color: red;'>❌ Vendor not found</div>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    if ($name && $email) {
        $update = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $update->execute([$name, $email, $id]);
        header("Location: manage_vendors.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Sub-Vendor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h3>Edit Sub-Vendor</h3>
    <form method="POST" class="card p-4 shadow-sm bg-white">
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($vendor['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($vendor['email']) ?>" required>
        </div>
        <button class="btn btn-primary">Update</button>
        <a href="manage_vendors.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
