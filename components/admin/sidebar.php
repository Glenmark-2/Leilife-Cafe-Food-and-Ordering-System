
<div id="db-container" class="collapsed"> <!-- default collapsed on mobile -->
    <div id="top" onclick="toggleSidebar()"> <!-- click to expand -->
        <div style="margin: auto;">
            <img id="logo" src="public/assests/Mask group.png" alt="logo">

        </div>
        <p>Leilife Cafe & Resto</p>
    </div>
    <?php
include "sidebar-button.php"; // make sure the path is correct

sidebarButton("public/assests/home.png", "Dashboard", "dashboard", "Dashboard logo");
sidebarButton("public/assests/sales.png", "Sales", "sales", "Sales logo");
sidebarButton("public/assests/fast-food.png", "Products", "products", "Products logo");
sidebarButton("public/assests/people.png", "Roles", "roles", "Roles logo");
sidebarButton("public/assests/messages.png", "Inbox", "inbox", "Inbox logo");
sidebarButton("public/assests/logout.png", "Sign out", "logout", "Sign out logo");

?>

</div>
<script src="Scripts/admin/components/sidebar.js"></script>