<?php
// backend/logout.php
session_start();

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Optionally, redirect to homepage
header("Location: /Leilife/public/index.php?page=home");
exit;
