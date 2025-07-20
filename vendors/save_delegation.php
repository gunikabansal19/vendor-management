<?php
require_once '../utils/auth_check.php';
require_once '../config/db.php';

if ($_SESSION['role'] !== 'super') {
    echo "Unauthorized";
    exit;
}

$vendor_id = $_POST['vendor_id'];
$can_add_driver = isset($_POST['can_add_driver']) ? 1 : 0;
$can_add_vehicle = isset($_POST['can_add_vehicle']) ? 1 : 0;
$can_upload_docs = isset($_POST['can_upload_docs']) ? 1 : 0;
$can_assign_vehicle = isset($_POST['can_assign_vehicle']) ? 1 : 0;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM delegations WHERE vendor_id = ?");
$stmt->execute([$vendor_id]);

if ($stmt->fetchColumn() > 0) {
    // update
    $update = $pdo->prepare("
        UPDATE delegations SET
            can_add_driver = ?, can_add_vehicle = ?,
            can_upload_docs = ?, can_assign_vehicle = ?
        WHERE vendor_id = ?
    ");
    $update->execute([$can_add_driver, $can_add_vehicle, $can_upload_docs, $can_assign_vehicle, $vendor_id]);
} else {
    // insert
    $insert = $pdo->prepare("
        INSERT INTO delegations (vendor_id, can_add_driver, can_add_vehicle, can_upload_docs, can_assign_vehicle)
        VALUES (?, ?, ?, ?, ?)
    ");
    $insert->execute([$vendor_id, $can_add_driver, $can_add_vehicle, $can_upload_docs, $can_assign_vehicle]);
}

header("Location: manage_delegation.php");
exit;
