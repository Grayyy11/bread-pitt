<?php
// about.php - About Us page
$pageTitle = "About Us";
include "includes/header.php";
?>

<section>
  <div class="wrap">
    <h1 class="title">About us</h1>
    <img class="banner" src="images/pastries.jpg" alt="A big picture of assorted pastries">
    <p style="text-align:center">We are <strong>Pan de Trio</strong>, the three students behind Bread Pitt. 2nd Year, BSIT 2A.</p>

    <div class="grid">
      <div class="card member">
        <img src="images/Byron_1.jpg" alt="Photo of Byron Paul Yumul">
        <h3>Byron Paul Yumul</h3>
        <p>Age: 30</p>
        <p><em>"Mapapapikit ka nalang sa Sarap"</em></p>
      </div>
      <div class="card member">
        <img src="images/Clyde_1.jpg" alt="Photo of Clyde Jolo Sison">
        <h3>Clyde Jolo Sison</h3>
        <p>Age: 18</p>
        <p><em>"Wala eh Masarap lang"</em></p>
      </div>
      <div class="card member">
        <img src="images/Ryan_1.jpg" alt="Photo of Ryan Joseph Guevarra">
        <h3>Ryan Joseph Guevarra</h3>
        <p>Age: 22</p>
        <p><em>"Isang Kagat, Busog Agad!"</em></p>
      </div>
    </div>
  </div>
</section>

<?php include "includes/footer.php"; ?>