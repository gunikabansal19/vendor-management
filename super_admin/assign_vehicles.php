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

// Handle unassign
if (isset($_GET['unassign_vehicle_id'])) {
    $unassign_id = (int) $_GET['unassign_vehicle_id'];
    $stmt = $pdo->prepare("UPDATE vehicles SET assigned_driver_id = NULL WHERE id = ?");
    $stmt->execute([$unassign_id]);
    header("Location: assign_vehicles.php");
    exit;
}

$message = $error = "";

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

// Handle vehicle assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['driver_id'], $_POST['vehicle_id']) && !isset($_POST['change_vendor'])) {
    $driver_id = $_POST['driver_id'];
    $vehicle_id = $_POST['vehicle_id'];

    try {
        $stmt = $pdo->prepare("UPDATE vehicles SET assigned_driver_id = ? WHERE id = ?");
        $stmt->execute([$driver_id, $vehicle_id]);
        $message = "✅ Vehicle assigned successfully.";
    } catch (PDOException $e) {
        $error = "❌ Error: " . $e->getMessage();
    }
}

// Fetch data
$drivers = $pdo->query("SELECT id, name FROM drivers")->fetchAll();
$vehicles = $pdo->query("SELECT id, registration_no FROM vehicles WHERE assigned_driver_id IS NULL")->fetchAll();
$vendors = $pdo->query("SELECT id, name FROM users")->fetchAll();

$assignedVehicles = $pdo->query("
    SELECT v.id, v.registration_no, d.name AS driver_name, v.vendor_id 
    FROM vehicles v 
    JOIN drivers d ON v.assigned_driver_id = d.id
")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Vehicles</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            background-color: #f7f7f7;
        }
        h2 {
            color: #333;
        }
        .msg {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        form {
            margin-bottom: 20px;
            background: #fff;
            padding: 15px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        select, button {
            padding: 6px 10px;
            margin: 5px;
            font-size: 14px;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        table {
            width: 100%;
            background: #fff;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 10px 14px;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f1f1f1;
        }
        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #333;
            background-color: #e2e6ea;
            padding: 8px 14px;
            border-radius: 5px;
        }
        .back-btn:hover {
            background-color: #d6d8db;
        }
    </style>
</head>
<body>

<a href="../public/dashboard.php" class="back-btn">← Back to Dashboard</a>

<h2>Assign Vehicle to Driver</h2>

<?php if ($message): ?>
    <div class="msg success"><?= $message ?></div>
<?php elseif ($error): ?>
    <div class="msg error"><?= $error ?></div>
<?php endif; ?>

<form method="POST">
    <label>Vehicle:
        <select name="vehicle_id" required>
            <option value="">-- Select Vehicle --</option>
            <?php foreach ($vehicles as $v): ?>
                <option value="<?= $v['id'] ?>"><?= $v['registration_no'] ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Driver:
        <select name="driver_id" required>
            <option value="">-- Select Driver --</option>
            <?php foreach ($drivers as $d): ?>
                <option value="<?= $d['id'] ?>"><?= $d['name'] ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button type="submit">Assign</button>
</form>

<h2>Assigned Vehicles</h2>

<?php if (empty($assignedVehicles)): ?>
    <p>No vehicles are currently assigned.</p>
<?php else: ?>
    <table>
        <tr>
            <th>Vehicle No</th>
            <th>Driver</th>
            <th>Vendor</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($assignedVehicles as $v): ?>
            <tr>
                <td><?= $v['registration_no'] ?></td>
                <td><?= $v['driver_name'] ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="vehicle_id" value="<?= $v['id'] ?>">
                        <select name="new_vendor_id">
                            <?php foreach ($vendors as $vendor): ?>
                                <option value="<?= $vendor['id'] ?>" <?= $vendor['id'] == $v['vendor_id'] ? 'selected' : '' ?>>
                                    <?= $vendor['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="change_vendor">Change</button>
                    </form>
                </td>
                <td>
                    <a href="?unassign_vehicle_id=<?= $v['id'] ?>" onclick="return confirm('Unassign this vehicle?')">Unassign</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

</body>
</html>
