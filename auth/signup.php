<?php
require_once '../config/db.php';

// Initialize message variables
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $parent_id = !empty($_POST["parent_id"]) ? $_POST["parent_id"] : null;

    // Map UI role to backend logic
    $role = $_POST["role"] ?? 'local';
    $type = "sub_vendor";

    // Check if user already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);

    if ($stmt->rowCount() > 0) {
        $error = "User with this email already exists!";
    } else {
        // Insert user
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, vendor_id)
                               VALUES (:name, :email, :password, :role, :type, :vendor_id)");
        $result = $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'type' => $type,
            'vendor_id' => $parent_id
        ]);

        if ($result) {
            $success = "Signup successful! You can now <a href='login.php'>login</a>.";
        } else {
            $error = "Signup failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vendor Signup</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #eef1f5;
        }
        .card {
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
        }
        .form-label {
            font-weight: 500;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white text-center">
                    <h4>Vendor Signup</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Vendor Role</label>
                            <select name="role" class="form-select" required>
                                <option value="">Select Role</option>
                                <option value="regional">Regional</option>
                                <option value="city">City</option>
                                <option value="local">Local</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Parent Vendor ID (Optional)</label>
                            <input type="number" name="parent_id" class="form-control">
                            <small class="form-text text-muted">Leave blank if registering as Sub Vendor.</small>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Sign Up</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="login.php">Already have an account? Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
