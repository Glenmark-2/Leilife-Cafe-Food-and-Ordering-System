<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();

// --- DOWNLOAD HANDLER (WITH TOKEN) ---
if (isset($_GET['page']) && isset($_GET['download']) && isset($_GET['token'])) {
    if (!isset($_SESSION['admin_id'])) die("Access denied.");
    if (!isset($_SESSION['download_token'])) die("Token not found.");
    if ($_GET['token'] !== $_SESSION['download_token']) die("Invalid token.");

    $fileMap = [
        'sales-report-pdf'   => __DIR__ . '/../pages/admin/sales-report-pdf.php',
        'sales-report-excel' => __DIR__ . '/../pages/admin/sales-report-excel.php',
        'sales-report-csv'   => __DIR__ . '/../pages/admin/sales-report-csv.php',
    ];

    if (isset($fileMap[$_GET['page']]) && file_exists($fileMap[$_GET['page']])) {
        include $fileMap[$_GET['page']];
        $_SESSION['download_token'] = bin2hex(random_bytes(16)); // regenerate token
        exit;
    } else {
        die("Download file not found.");
    }
}

// --- DOWNLOAD HANDLER (WITHOUT TOKEN) ---
if (isset($_GET['page']) && isset($_GET['download']) && $_GET['page'] === 'pos-receipt') {
    if (!isset($_SESSION['admin_id'])) die("Access denied.");

    $file = __DIR__ . '/../pages/admin/pos-receipt.php';
    if (file_exists($file)) {
        include $file; // this script generates and outputs the PDF
        exit;
    } else {
        die("Receipt file not found.");
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
