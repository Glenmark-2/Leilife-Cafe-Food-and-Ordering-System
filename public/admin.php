<?php
// === PRE-HANDLER: intercept Excel or PDF downloads before any HTML output ===
if (isset($_GET['page'])) {
    $page = $_GET['page'];

    // Handle Excel download
    if ($page === 'sales-report-excel' && isset($_GET['download'])) {
        include "../pages/admin/sales-report-excel.php";
        exit; // Stop further output to prevent HTML interference
    }

    // (optional) Handle PDF download similarly if needed
    if ($page === 'sales-report-pdf' && isset($_GET['download'])) {
        include "../pages/admin/sales-report-pdf.php";
        exit;
    }
}

// === NORMAL PAGE ROUTING ===
$currentPage = $_GET['page'] ?? 'dashboard';
$page = $currentPage;
?>

<div id="container"> <!-- main flex container -->
    <div id="sidebar-wrapper">
        <?php include "../components/admin/header.php"; // contains sidebar ?>
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
            'sales-report-pdf',
            'sales-report-excel'
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
