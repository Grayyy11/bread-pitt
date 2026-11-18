<?php
// Top of every page = head and navbar
include_once __DIR__ . "/auth.php";

if (!isset($pageTitle)) {
    $pageTitle = "Bread Pitt";
}

// name of the current file
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

      <?php if (isLoggedIn()) { ?>
        <?php if ($_SESSION['role'] == "admin") { ?>
          <li><a href="admin.php" <?php if ($currentPage == "admin.php") echo 'class="active"'; ?>>Admin</a></li> <?php } ?>
      <li><a href="users.php" <?php if ($currentPage == "users.php") echo 'class="active"'; ?>>Users</a></li>
                <?php if ($_SESSION['role'] == "customer") { ?>
          <li><a href="cart.php" <?php if ($currentPage == "cart.php") echo 'class="active"'; ?>>Cart</a></li>
        <?php } ?>
        <li><a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['full_name']); ?>)</a></li>
      <?php } else { ?>
        <li><a href="login.php" <?php if ($currentPage == "login.php") echo 'class="active"'; ?>>Login</a></li>
        <li><a href="register.php" <?php if ($currentPage == "register.php") echo 'class="active"'; ?>>Register</a></li>
      <?php } ?>
    </ul>
  </nav>

  <main>