<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Leilife's Admin</title>

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="/Leilife/assests/Mask%20group.png">
  <link rel="shortcut icon" type="image/png" href="/Leilife/assests/Mask%20group.png">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Base path -->
  <base href="/Leilife/">

  <!-- Global Admin CSS -->
  <link rel="stylesheet" href="CSS/admin/components/sidebar.css">
  <link rel="stylesheet" href="CSS/admin/components/sidebar-button.css">
  <link rel="stylesheet" href="CSS/admin/components/header.css">

  <!-- Page-Specific -->
  <?php
  // Correct relative include path (from components/admin/header.php → backend/config/style_config_admin.php)
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

