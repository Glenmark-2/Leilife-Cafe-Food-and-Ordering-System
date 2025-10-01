<?php 
function requireDriverLogin($page) {
    if (!isset($_SESSION['driver_id'])) {
        header('Location: /leilife/pages/admin/login-driver-123.php');
        exit;
    }
}
