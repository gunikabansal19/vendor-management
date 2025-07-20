<?php
// File: drivers/upload_docs_process.php
session_start();
require_once '../config/db.php';

$vendor_id = $_SESSION['vendor_id'] ?? null;
$role = $_SESSION['role'] ?? 'local';
if (!$vendor_id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Access denied or invalid request.";
    exit;
}

//  Super Vendor can skip delegation check
if ($role !== 'super') {
    $permStmt = $pdo->prepare("SELECT can_upload_docs FROM delegations WHERE vendor_id = ?");
    $permStmt->execute([$vendor_id]);
    $permissions = $permStmt->fetch();

    if (!$permissions || $permissions['can_upload_docs'] != 1) {
        echo "<div style='padding:20px;color:red;'>❌ Access Denied: You are not allowed to upload driver documents.</div>";
        exit;
    }
}

//  Get and validate input
$driver_id = $_POST['driver_id'] ?? null;
$expiry_date = $_POST['expiry_date'] ?? null;
$doc_type = $_POST['doc_type'] ?? 'DL';

if (!$driver_id || !$expiry_date || !isset($_FILES['document'])) {
    echo "Missing required fields.";
    exit;
}

//  Upload file
$uploadDir = '../uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}
$filename = basename($_FILES['document']['name']);
$targetFile = $uploadDir . time() . '_' . preg_replace('/\s+/', '_', $filename);

if (!move_uploaded_file($_FILES['document']['tmp_name'], $targetFile)) {
    echo "Failed to upload file.";
    exit;
}

//  Save into database
$stmt = $pdo->prepare("INSERT INTO driver_docs (driver_id, doc_type, doc_file, expiry_date) VALUES (?, ?, ?, ?)");
$stmt->execute([$driver_id, $doc_type, $targetFile, $expiry_date]);

header("Location: upload_docs.php?success=1");
exit;
?>
