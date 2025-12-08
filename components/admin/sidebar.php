<div id="db-container"> <!-- default collapsed on mobile -->
    <div id="top"> <!-- click to expand -->
        <div style="margin: auto;">
            <img id="logo" src="public/assests/Mask group.png" alt="logo">

        </div>
        <p>Leilife Cafe & Resto</p>
    </div>
    <?php
    include "sidebar-button.php"; // make sure the path is correct
    require_once __DIR__ . '/../../backend/db_script/appData.php';
    require_once __DIR__ . '/../../backend/db_script/db.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $appData = new AppData($pdo);
    $currentAdmin = $appData->getCurrentAdmin();

    if ($currentAdmin) {
        sidebarButton("public/assests/home.png", "Dashboard", "dashboard", "Dashboard logo");
        sidebarButton("public/assests/fast-food.png", "Products", "products", "Products logo");
        sidebarButton("public/assests/people.png", "Staffs", "roles", "Roles logo");
        sidebarButton("public/assests/messages.png", "Inbox", "inbox", "Inbox logo");

        if ($currentAdmin['isMainAdmin']) {
            sidebarButton("public/assests/sales.png", "Sales", "sales", "Sales logo");
            sidebarButton("public/assests/analytics.png", "Analytics", "reports", "Reports logo");
            sidebarButton("public/assests/settings.png", "Settings", "settings", "Settings logo");
        }

        sidebarButton("public/assests/logout.png", "Sign out", "logout", "Sign out logo");

    }

    ?>

</div>
<script>
    
</script>
<script src="Scripts/admin/components/sidebar.js"></script>