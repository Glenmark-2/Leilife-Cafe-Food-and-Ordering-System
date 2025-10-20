<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();

// --- DOWNLOAD HANDLER ---
if (isset($_GET['page']) && isset($_GET['download'])) {
    if (!isset($_SESSION['admin_id'])) die("Access denied.");
    if (!isset($_SESSION['download_token'])) die("Token not found.");
    if (!isset($_GET['token']) || $_GET['token'] !== $_SESSION['download_token'])
        die("Invalid token.");

    $fileMap = [
        'sales-report-pdf'   => __DIR__ . '/../pages/admin/sales-report-pdf.php',
        'sales-report-excel' => __DIR__ . '/../pages/admin/sales-report-excel.php',
        'sales-report-csv' => __DIR__ . '/../pages/admin/sales-report-csv.php'
    ];

    if (isset($fileMap[$_GET['page']]) && file_exists($fileMap[$_GET['page']])) {
        include $fileMap[$_GET['page']];

        // --- regenerate token AFTER successful download ---
        $_SESSION['download_token'] = bin2hex(random_bytes(16));
        exit; // stop further HTML
    } else {
        die("Download file not found.");
    }
}

// === NORMAL PAGE ROUTING ===
$currentPage = $_GET['page'] ?? 'dashboard';
$page = $currentPage;
?>

<div id="container"> <!-- main flex container -->
    <div id="sidebar-wrapper">
        <?php include "../components/admin/header.php"; ?>
    </div>

    <div id="content-wrapper">
        <?php
        $allowed_pages = [
            'dashboard',
            'sales',
            'products',
            'roles',
            'inbox',
            'reports',
            'audit',
        ];

        if (in_array($currentPage, $allowed_pages)) {
            include "../pages/admin/$currentPage.php";
        } else {
            echo "<h1>Page not found</h1>";
        }
        ?>
    </div>
</div>

</body>
</html>
