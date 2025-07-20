<?php
session_start();
require_once '../config/db.php';
require_once '../utils/auth_check.php';

$vendor_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'local';

// Permission check
if ($role !== 'super') {
    $permStmt = $pdo->prepare("SELECT can_assign_vehicle FROM delegations WHERE vendor_id = ?");
    $permStmt->execute([$vendor_id]);
    $permissions = $permStmt->fetch();

    if (!$permissions || $permissions['can_assign_vehicle'] != 1) {
        echo "<div style='padding:20px; color:red;'>❌ Access Denied: You cannot assign vehicles.</div>";
        exit;
    }
}

// Handle unassign request
if (isset($_GET['unassign_vehicle_id'])) {
    $unassign_id = (int) $_GET['unassign_vehicle_id'];
    $stmt = $pdo->prepare("UPDATE vehicles SET assigned_driver_id = NULL WHERE id = ?");
    $stmt->execute([$unassign_id]);
    header("Location: assign_vehicles.php");
    exit;
}

// Handle vendor change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_vendor'])) {
    $vehicle_id = (int)$_POST['vehicle_id'];
    $new_vendor_id = (int)$_POST['new_vendor_id'];

    try {
        $stmt = $pdo->prepare("UPDATE vehicles SET vendor_id = ? WHERE id = ?");
        $stmt->execute([$new_vendor_id, $vehicle_id]);
        $message = "✅ Vendor changed successfully.";
    } catch (PDOException $e) {
        $error = "❌ Error changing vendor: " . $e->getMessage();
    }
}

// Handle assign vehicle to driver
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['driver_id'], $_POST['vehicle_id']) && !isset($_POST['change_vendor'])) {
    $driver_id = $_POST['driver_id'];
    $vehicle_id = $_POST['vehicle_id'];

    if ($driver_id && $vehicle_id) {
        try {
            $stmt = $pdo->prepare("UPDATE vehicles SET assigned_driver_id = ? WHERE id = ?");
            $stmt->execute([$driver_id, $vehicle_id]);
            $message = "✅ Vehicle assigned successfully.";
        } catch (PDOException $e) {
            $error = "❌ Error: " . $e->getMessage();
        }
    } else {
        $error = "❌ Please select both a driver and a vehicle.";
    }
}

$drivers = $pdo->query("SELECT id, name FROM drivers")->fetchAll();
$vehicles = $pdo->query("SELECT id, registration_no FROM vehicles WHERE assigned_driver_id IS NULL")->fetchAll();
$vendors = $pdo->query("SELECT id, name FROM vendors")->fetchAll();

$assignedVehicles = $pdo->query("
    SELECT v.id, v.registration_no, d.name AS driver_name, v.vendor_id 
    FROM vehicles v 
    JOIN drivers d ON v.assigned_driver_id = d.id
")->fetchAll();
?>
