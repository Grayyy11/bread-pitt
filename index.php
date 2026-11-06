<?php
// index.php - Home page
$pageTitle = "Home";
include "includes/header.php";
?>

<!-- HOME / HERO -->
<section class="hero">
  <img src="images/pastries.jpg" alt="Assorted freshly baked pastries">
  <div class="hero-text">
    <h1>Fresh Bread, Baked with Love</h1>
    <p>Welcome to Bread Pitt, where every bite is warm, soft, and made fresh daily.</p>
    <a href="menu.php" class="btn">See our menu</a>
  </div>
</section>

<section>
  <div class="wrap">
    <h2 class="title">Why choose Bread Pitt?</h2>
    <div class="grid">
      <div class="card">
        <h3>Baked fresh</h3>
        <p>Our breads and pastries come out of the oven every morning.</p>
      </div>
      <div class="card">
        <h3>Simple ingredients</h3>
        <p>Flour, butter, yeast, and real flavors. Nothing extra.</p>
      </div>
      <div class="card">
        <h3>Made with love</h3>
        <p>Every recipe is made with care by our team.</p>
      </div>
    </div>
  </div>
</section>

<?php include "includes/footer.php"; ?>