<?php
$page_title = $page_title ?? 'VetSystem';
$current_page = basename($_SERVER['PHP_SELF'] ?? '');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?=h($page_title)?> | VetSystem</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="dashboard.php"><span class="brand-mark">VS</span><span><b>VetSystem</b><small>Naic Municipal Veterinary Office</small></span></a>
  <nav class="main-nav">
    <a class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">Dashboard</a>
    <a class="<?= $current_page === 'livestock.php' ? 'active' : '' ?>" href="livestock.php">Farms</a>
    <a class="<?= $current_page === 'monitoring.php' ? 'active' : '' ?>" href="monitoring.php">Monitoring</a>
    <a class="<?= $current_page === 'gis_map.php' ? 'active' : '' ?>" href="gis_map.php">GIS Map</a>
  </nav>
  <div class="user-menu"><span><?=h($_SESSION['name'] ?? 'User')?></span><a href="logout.php">Log out</a></div>
</header>
<main class="page-shell">
  <div class="page-heading"><div><p class="eyebrow">LIVESTOCK & POULTRY MONITORING</p><h1><?=h($page_title)?></h1></div></div>
