<?php
$currentPage = $_GET['page'] ?? 'dashboard';
?>

<div id="container"> <!-- main flex container -->
    <div id="sidebar-wrapper">
        <?php include "../components/admin/header.php"; // contains sidebar ?>
    </div>

    <div id="content-wrapper">
        <?php
        $allowed_pages = ['dashboard', 'sales', 'products', 'roles', 'inbox'];
        if (in_array($currentPage, $allowed_pages)) {
            include"../pages/admin/$currentPage.php";
        } else {
            echo "<h1>Page not found</h1>";
        }
        ?>
    </div>
</div>

</body>
</html>
