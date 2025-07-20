<?php
// File: drivers/view_docs.php
session_start();
require_once '../config/db.php';
require_once '../utils/auth_check.php';

$role = $_SESSION['role'] ?? 'local';
$vendor_id = $_SESSION['user_id'] ?? null;
$canUploadDocs = false;
if (!$vendor_id) die("Access denied.");

if ($role !== 'super') {
    $permStmt = $pdo->prepare("SELECT can_upload_docs FROM delegations WHERE vendor_id = ?");
    $permStmt->execute([$vendor_id]);
    $perm = $permStmt->fetch();
    $canUploadDocs = $perm && $perm['can_upload_docs'] == 1;
} else {
    $canUploadDocs = true; // Super vendor can always add drivers
}

if($role == 'super'){
    $stmt = $pdo->query("SELECT d.name AS driver_name, d.id AS driver_id, docs.* 
                       FROM driver_docs docs 
                       INNER JOIN drivers d ON docs.driver_id = d.id 
                       ORDER BY docs.uploaded_on DESC");
$all_docs = $stmt->fetchAll();
}else{
    $stmt = $pdo->prepare("SELECT d.name AS driver_name, d.id AS driver_id, docs.* 
                       FROM driver_docs docs 
                       INNER JOIN drivers d ON docs.driver_id = d.id 
                       WHERE d.vendor_id = ? 
                       ORDER BY docs.uploaded_on DESC");
    $stmt->execute([$vendor_id]);
    $all_docs = $stmt->fetchAll();
}

// Separate active and expired documents
$active_docs = [];
$expired_docs = [];

foreach ($all_docs as $doc) {
    if (strtotime($doc['expiry_date']) < time()) {
        $expired_docs[] = $doc;
    } else {
        $active_docs[] = $doc;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Driver Documents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-folder2-open"></i> Driver Documents</h2>
        <?php if ($canUploadDocs): ?>
            <a href="upload_docs.php" class="btn btn-success">
                   <i class="bi bi-upload"></i> Upload New Document
               </a>
            <?php endif; ?>
    </div>

    <!-- ACTIVE DOCUMENTS -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="bi bi-file-earmark-text"></i> Active Driver Documents</h4>
        </div>
        <div class="card-body">
            <?php if (empty($active_docs)): ?>
                <div class="alert alert-info text-center">No active documents available.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-success">
                        <tr>
                            <th>Driver</th>
                            <th>Document Type</th>
                            <th>Expiry Date</th>
                            <th>File</th>
                            <th>Uploaded On</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($active_docs as $doc): ?>
                            <tr>
                                <td><?= htmlspecialchars($doc['driver_name']) ?></td>
                                <td><?= htmlspecialchars($doc['doc_type']) ?></td>
                                <td><?= date('Y-m-d', strtotime($doc['expiry_date'])) ?></td>
                                <td>
                                    <?php if (file_exists($doc['doc_file'])): ?>
                                        <a href="<?= htmlspecialchars($doc['doc_file']) ?>" target="_blank">View</a>
                                    <?php else: ?>
                                        <span class="text-muted">File missing</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('Y-m-d H:i', strtotime($doc['uploaded_on'])) ?></td>
                                <td>
                                    <a href="upload_docs.php?driver_id=<?= $doc['driver_id'] ?>&doc_type=<?= urlencode($doc['doc_type']) ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        Upload New
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- EXPIRED DOCUMENTS -->
    <div class="card shadow-sm">
        <div class="card-header bg-danger text-white">
            <h4 class="mb-0"><i class="bi bi-exclamation-circle"></i> Expired Documents</h4>
        </div>
        <div class="card-body">
            <?php if (empty($expired_docs)): ?>
                <div class="alert alert-success text-center">No expired documents found.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-danger">
                        <tr>
                            <th>Driver</th>
                            <th>Document Type</th>
                            <th>Expiry Date</th>
                            <th>File</th>
                            <th>Uploaded On</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($expired_docs as $doc): ?>
                            <tr>
                                <td><?= htmlspecialchars($doc['driver_name']) ?></td>
                                <td><?= htmlspecialchars($doc['doc_type']) ?></td>
                                <td><?= date('Y-m-d', strtotime($doc['expiry_date'])) ?></td>
                                <td>
                                    <?php if (file_exists($doc['doc_file'])): ?>
                                        <a href="<?= htmlspecialchars($doc['doc_file']) ?>" target="_blank">View</a>
                                    <?php else: ?>
                                        <span class="text-muted">File missing</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('Y-m-d H:i', strtotime($doc['uploaded_on'])) ?></td>
                                <td>
                                    <a href="upload_docs.php?driver_id=<?= $doc['driver_id'] ?>&doc_type=<?= urlencode($doc['doc_type']) ?>" 
                                       class="btn btn-sm btn-outline-danger">
                                        Upload New
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <a href="../public/dashboard.php" class="btn btn-outline-secondary mt-4 me-2">
            <i class="bi bi-arrow-left-circle"></i> Back to Dashboard
        </a>

</div>

</body>
</html>
