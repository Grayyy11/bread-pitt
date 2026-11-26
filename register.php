<?php
// Register page: creates a new customer account
$pageTitle = "Register";
include_once "includes/db.php";

$errors = [];
$success = false;
$full_name = "";
$email = "";

// run only when the form is submitted
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $full_name = trim($_POST['full_name'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";
    $confirm = $_POST['confirm'] ?? "";

    // check the form
    if ($full_name == "" || $email == "" || $password == "") {
        $errors[] = "Please fill in all fields.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    if ($password != $confirm) {
        $errors[] = "Passwords do not match.";
    }

    // check if the email is already used
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "That email is already registered.";
        }
    }

    // save the new user (role 3 = customer)
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $role_id = 3;

        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$full_name, $email, $hash, $role_id]);
        $success = true;
    }
}

include "includes/header.php";
?>

<section>
  <div class="wrap">
    <h1 class="title">Create an account</h1>

    <?php if ($success) { ?>
      <p class="message ok">Account created! You can now log in.</p>
    <?php } else { ?>

      <?php foreach ($errors as $error) { ?>
        <p class="message error"><?php echo htmlspecialchars($error); ?></p>
      <?php } ?>

      <form method="post" action="register.php">
        <label for="full_name">Full name</label>
        <input type="text" id="full_name" name="full_name"
               value="<?php echo htmlspecialchars($full_name); ?>">

        <label for="email">Email</label>
        <input type="email" id="email" name="email"
               value="<?php echo htmlspecialchars($email); ?>">

        <label for="password">Password</label>
        <input type="password" id="password" name="password">

        <label for="confirm">Confirm password</label>
        <input type="password" id="confirm" name="confirm">

        <button type="submit" class="btn" style="margin-top:15px">Register</button>
      </form>

    <?php } ?>
  </div>
</section>

<?php include "includes/footer.php"; ?>