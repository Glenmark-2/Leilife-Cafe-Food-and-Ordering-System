<?php 
function sidebarButton($imagePath, $text, $page, $alt = "") {
    $currentPage = $_GET['page'] ?? 'dashboard';
    $active = ($currentPage === $page) ? 'active' : '';

    $href = ($page === 'logout') ? '/Leilife/backend/admin/admin_logout.php' : "/Leilife/public/admin.php?page=$page";

    echo "
    <div id='box'>
        <a href='$href' class='sidebar-btn $active' style='text-decoration:none; width:100%;'>
            <img src='$imagePath' alt='$alt'>
            <p id='text'>$text</p>
        </a>
    </div>
    ";
}

?>
