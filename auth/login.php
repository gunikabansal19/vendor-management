<?php
session_start();
require_once '../config/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // ✅ Use the correct table: users (not vendors)
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // ✅ Set session values
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['vendor_id'] = $user['vendor_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['type'] = $user['type'];
        $_SESSION['user_name'] = $user['name'];

        // ✅ Redirect to dashboard
        header("Location: ../public/dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vendor Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .card {
            border-radius: 1rem;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center align-items-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">Vendor Login</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger text-center"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address:</label>
                            <input type="email" name="email" id="email" class="form-control" required placeholder="Enter your email">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password:</label>
                            <input type="password" name="password" id="password" class="form-control" required placeholder="Enter your password">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <a href="signup.php" class="text-decoration-none">Don’t have an account? Sign up</a>
                    </div>
                </div>
                <div class="card-footer text-center text-muted small">
                    Vendor Management System
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
