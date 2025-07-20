<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if vendor is logged in
if (!isset($_SESSION['vendor_id']) || empty($_SESSION['vendor_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Optionally define default role
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'local'; // fallback role
}
?>
