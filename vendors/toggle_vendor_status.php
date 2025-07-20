<?php
session_start();
require_once '../config/db.php';

// ✅ Check if user is super_vendor
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
    // ✅ Check if vendor exists
    $stmt = $pdo->prepare("SELECT status FROM vendors WHERE id = ?");
    $stmt->execute([$id]);
    $vendor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($vendor) {
        // ✅ Toggle status
        $newStatus = ($vendor['status'] === 'active') ? 'inactive' : 'active';

        $updateStmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $updateStmt->execute([$newStatus, $id]);

        // Optional: flash message (requires flash system)
        $_SESSION['flash_message'] = "Vendor status updated to '{$newStatus}'.";
    }

} catch (PDOException $e) {
    // Optional: Log the error
    error_log("Status toggle failed: " . $e->getMessage());
    $_SESSION['flash_message'] = "❌ Database error occurred.";
}

header("Location: manage_vendors.php");
exit;
