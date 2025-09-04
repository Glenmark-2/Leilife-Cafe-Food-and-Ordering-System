
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Base path for all relative links -->
    <base href="/leilife/">

    <!-- Always needed (global admin CSS) -->
    <link rel="stylesheet" href="CSS/admin/components/sidebar.css">
    <link rel="stylesheet" href="CSS/admin/components/sidebar-button.css">
    <link rel="stylesheet" href="CSS/admin/components/header.css">

    <!-- Page-Specific -->
    <?php
    $page_styles_admin = include __DIR__ . '/../../backend/config/style_config_admin.php';

    if (isset($page) && isset($page_styles_admin[$page])) {
        foreach ($page_styles_admin[$page] as $css_file) {
            echo '<link rel="stylesheet" href="' . $css_file . '">' . PHP_EOL;
        }
    } else {
        echo "<!-- No CSS found for page: $page -->" . PHP_EOL;
    }
    ?>
</head>

<body>
    <div id="sidebar-wrapper">
        <?php include __DIR__ . "/sidebar.php"; ?>
    </div>



