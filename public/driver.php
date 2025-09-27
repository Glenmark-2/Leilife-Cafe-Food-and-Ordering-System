<?php
$currentPage = $_GET['page'] ?? 'home';
$page = $currentPage;
?>

<div> <!-- main flex container -->
    <div>
        <?php include "../components/driver/header.php"; // contains sidebar ?>
    </div>

    <div id="content-wrapper">
        <?php
        $allowed_pages = ['change-pass', 'home'];
        if (in_array($currentPage, $allowed_pages)) {
            include"../pages/driver/$currentPage.php";
        } else {
            echo "<h1>Page not found</h1>";
        }
        ?>
    </div>
</div>

</body>
</html>
