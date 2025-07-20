<?php
require_once '../utils/auth_check.php';
require_once '../config/db.php';

$vendor_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'local';

// Redirect if not super admin
if ($role !== 'super') {
    echo "Access denied.";
    exit;
}

// Function to recursively get sub-vendors
function fetchSubVendors($pdo, $parent_id, $level = 0) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE vendor_id = ?");
    $stmt->execute([$parent_id]);
    $subVendors = $stmt->fetchAll();

    $html = '';
    foreach ($subVendors as $vendor) {
        $indent = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $level);
        $html .= "<tr>
            <td>{$indent}↳ " . htmlspecialchars($vendor['name']) . "</td>
            <td>" . htmlspecialchars($vendor['email']) . "</td>
            <td>" . htmlspecialchars($vendor['role']) . "</td>
            <td>" . htmlspecialchars($vendor['created_at']) . "</td>
        </tr>";
        // Recursive call
        $html .= fetchSubVendors($pdo, $vendor['id'], $level + 1);
    }
    return $html;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vendor Hierarchy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h3 class="mb-4">Vendor Hierarchy</h3>

    <table class="table table-bordered table-striped shadow-sm">
        <thead class="table-light">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Super vendor info
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$vendor_id]);
            $super = $stmt->fetch();

            echo "<tr>
                <td><strong>" . htmlspecialchars($super['name']) . "</strong></td>
                <td>" . htmlspecialchars($super['email']) . "</td>
                <td>" . htmlspecialchars($super['role']) . "</td>
                <td>" . htmlspecialchars($super['created_at']) . "</td>
            </tr>";

            // Sub-vendors tree
            echo fetchSubVendors($pdo, $vendor_id);
            ?>
        </tbody>
    </table>
</div>
</body>
</html>
