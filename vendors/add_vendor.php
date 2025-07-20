<?php
require_once '../utils/auth_check.php';
require_once '../config/db.php';

$parent_id = $_SESSION['vendor_id'];
$role = $_SESSION['role'] ?? 'local';
$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $sub_role = $_POST['role'] ?? 'local';

    // Check if email already exists
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM vendors WHERE email = ?");
    $checkStmt->execute([$email]);
    $emailExists = $checkStmt->fetchColumn();

    if ($emailExists) {
        $error = "Email already exists. Try another.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (vendor_id, name, email, password, role, type) VALUES (?, ?, ?, ?, ?, 'sub_vendor')");
        $stmt->execute([$parent_id, $name, $email, $password, $sub_role]);
        $message = "Sub vendor added successfully!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Vendor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4>Add Sub-Vendor</h4>
        </div>
        <div class="card-body">
            <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php elseif ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Role</label>
                    <select name="role" class="form-select">
                        <option value="vendor">Vendor</option>
                        <option value="manager">Manager</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Add Vendor</button>
                <a href="manage_vendors.php" class="btn btn-secondary ms-2">Back</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
