<?php
session_start();
require_once '../config/db.php';
require_once '../utils/auth_check.php';

$role = $_SESSION['role'] ?? '';
$vendor_id = $_SESSION['vendor_id'] ?? null;

if ($role !== 'super') {
    echo "<div style='padding: 20px; color: red;'>❌ Access Denied: Only admin can add sub-vendors.</div>";
    exit;
}

$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = strtolower(trim($_POST['email']));
    $role = $_POST['role'] ?? 'local';
    $passwordRaw = $_POST['password'] ?? '';
    $password = password_hash($passwordRaw, PASSWORD_BCRYPT);

    if ($name && $email && $passwordRaw) {
        try {
            // Check if email already exists
            $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $check->execute([$email]);

            if ($check->fetchColumn() > 0) {
                $error = "❌ Error: This email already exists. Please use a different one.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, type, vendor_id, status) VALUES (?, ?, ?, ?, 'sub_vendor', ?, 'active')");
                $stmt->execute([$name, $email, $password, $role, $vendor_id]);
                $message = " Sub-vendor added successfully!";
            }
        } catch (PDOException $e) {
            $error = "❌ Database error: " . $e->getMessage();
        }
    } else {
        $error = "❌ All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Sub-Vendor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">➕ Add Sub-Vendor</h4>
        </div>
        <div class="card-body">
            <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php elseif ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">👤 Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="Enter full name">
                </div>
                <div class="mb-3">
                    <label class="form-label">📧 Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="Enter email">
                </div>
                <div class="mb-3">
                    <label class="form-label">🔒 Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Create password">
                </div>
                <button type="submit" class="btn btn-primary w-100">Add Sub-Vendor</button>
            </form>
        </div>
    </div>

    <div class="mt-3 text-center">
        <a href="manage_vendors.php" class="btn btn-outline-secondary btn-sm">← Back to Manage Vendors</a>
    </div>
</div>
</body>
</html>
