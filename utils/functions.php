<?php
function isSuperVendor() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'super';
}
?>