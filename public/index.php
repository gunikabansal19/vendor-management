<?php
// Optional server-side redirect fallback
header("Refresh: 2; URL=../auth/login.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Redirecting to Login...</title>
    <meta http-equiv="refresh" content="2;url=../auth/login.php" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="alert alert-info text-center shadow-sm">
        <h4 class="alert-heading">Redirecting...</h4>
        <p>You are being redirected to the <strong>Vendor Login</strong> page.</p>
        <hr>
        <p class="mb-0">
            If you are not redirected automatically, <a href="../auth/login.php" class="alert-link">click here</a>.
        </p>
    </div>
</div>

<noscript>
    <div class="container mt-3">
        <div class="alert alert-warning text-center">
            JavaScript is disabled. Please <a href="../auth/login.php" class="alert-link">click here</a> to continue.
        </div>
    </div>
</noscript>

</body>
</html>
