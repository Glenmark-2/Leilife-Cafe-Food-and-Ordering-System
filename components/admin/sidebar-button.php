<?php 
function sidebarButton($imagePath, $text, $page, $alt = "") {
    $currentPage = $_GET['page'] ?? 'dashboard';
    $active = ($currentPage === $page) ? 'active' : '';

    $href = ($page === 'logout') ? '/leilife/backend/admin/logout.php' : "/leilife/public/admin.php?page=$page";

    echo "
    <div style='display: flex; justify-content:flex-end; margin-top:10px'>
        <a href='$href' class='sidebar-btn $active' style='text-decoration:none; width:100%;'>
            <img src='$imagePath' alt='$alt'>
            <p id='text'>$text</p>
        </a>
    </div>
    ";
}

?>
