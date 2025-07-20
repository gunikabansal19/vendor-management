<?php
session_start();
require_once '../config/db.php';

// ✅ Only allow super vendors
if ($_SESSION['role'] !== 'super') {
    http_response_code(403);
    exit("❌ Unauthorized access.");
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: manage_vendors.php");
    exit;
}

try {
    // ✅ Check if sub-vendor exists in users table
    $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ? AND type = 'sub_vendor'");
    $stmt->execute([$id]);
    $vendor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($vendor) {
        // ✅ Toggle status
        $newStatus = ($vendor['status'] === 'active') ? 'inactive' : 'active';

        $updateStmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $updateStmt->execute([$newStatus, $id]);

        $_SESSION['flash_message'] = "✅ Vendor status updated to '{$newStatus}'.";
    } else {
        $_SESSION['flash_message'] = "❌ Vendor not found.";
    }

} catch (PDOException $e) {
    error_log("Status toggle failed: " . $e->getMessage());
    $_SESSION['flash_message'] = "❌ Database error occurred.";
}

header("Location: manage_vendors.php");
exit;
