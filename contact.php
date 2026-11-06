<?php
// contact.php - Contact page
$pageTitle = "Contact";
include "includes/header.php";
?>

<section>
  <div class="wrap">
    <h1 class="title">Contact us</h1>
    <form id="contactForm" novalidate>
      <label for="name">Your name</label>
      <input type="text" id="name" placeholder="Juan Dela Cruz">

      <label for="email">Email</label>
      <input type="email" id="email" placeholder="juan@email.com">

      <label for="msg">Message</label>
      <textarea id="msg" rows="5" placeholder="Tell us what you would like to order"></textarea>

      <p class="message" id="formMessage"></p>
      <button type="submit" class="btn" style="margin-top:15px">Send message</button>
    </form>
  </div>
</section>

<?php include "includes/footer.php"; ?>