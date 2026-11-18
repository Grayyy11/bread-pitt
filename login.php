<?php
// Login page
$pageTitle = "Login";
include_once "includes/db.php";
include_once "includes/auth.php";

$error = "";

// run only when the form is submitted
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // find the user and the role name
    $stmt = $conn->prepare("SELECT u.id, u.full_name, u.password_hash, u.is_active, r.name AS role
                            FROM users u
                            JOIN roles r ON r.id = u.role_id
                            WHERE u.email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && $user['is_active'] == 0) {
        $error = "This account is deactivated.";
    } elseif ($user && password_verify($password, $user['password_hash'])) {
        
        // correct password: save the user in the session
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];

        logAction($conn, $user['id'], "login", "User logged in");
        header("Location: index.php");
        exit;
    } else {
        // wrong email or password
        $error = "Wrong email or password.";
        $failedId = $user ? $user['id'] : null;
        logAction($conn, $failedId, "failed_login", "Failed login for " . $email);
    }
}

include "includes/header.php";
?>

<section>
  <div class="wrap">
    <h1 class="title">Login</h1>

    <?php if ($error != "") { ?>
      <p class="message error"><?php echo htmlspecialchars($error); ?></p>
    <?php } ?>

    <form method="post" action="login.php">
      <label for="email">Email</label>
      <input type="email" id="email" name="email">

      <label for="password">Password</label>
      <input type="password" id="password" name="password">

      <button type="submit" class="btn" style="margin-top:15px">Login</button>
    </form>

    <p style="text-align:center">No account yet? <a href="register.php">Register here</a></p>
  </div>
</section>

<?php include "includes/footer.php"; ?>