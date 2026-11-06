<?php
// includes/header.php
// Shared top part of every page: <head> + navbar.
// Each page sets $pageTitle before including this file.
if (!isset($pageTitle)) {
    $pageTitle = "Bread Pitt";
}

// Name of the current file, like "menu.php" (used to highlight the active link)
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $pageTitle; ?> | Bread Pitt</title>
  <link rel="stylesheet" href="css/bakery.css?v=2">
</head>
<body>

  <nav class="navbar">
    <a href="index.php" class="logo">Bread Pitt</a>

    <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu">&#9776;</button>

    <ul class="nav-links" id="navLinks">
      <li><a href="index.php" <?php if ($currentPage == "index.php") echo 'class="active"'; ?>>Home</a></li>
      <li><a href="menu.php" <?php if ($currentPage == "menu.php") echo 'class="active"'; ?>>Menu</a></li>
      <li><a href="about.php" <?php if ($currentPage == "about.php") echo 'class="active"'; ?>>About Us</a></li>
      <li><a href="contact.php" <?php if ($currentPage == "contact.php") echo 'class="active"'; ?>>Contact</a></li>
    </ul>
  </nav>

  <main>