<?php
// Staff page: check and update product availability
include_once "includes/db.php";
include_once "includes/auth.php";

// only staff and admin can open this page
requireRole(["staff", "admin"]);

$pageTitle = "Staff";

// run only when an Update button is clicked
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $id = (int)($_POST['id'] ?? 0);
    $stock_qty = $_POST['stock_qty'] ?? "";
    $is_available = (int)($_POST['is_available'] ?? 0);

    if (!ctype_digit((string)$stock_qty)) {
        $_SESSION['message'] = "Stock must be a whole number.";
    } else {
        // find the product name for the message and the audit log
        $stmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $p = $stmt->fetch();

        if ($p) {
            // change only the stock and availability
            $stmt = $pdo->prepare("UPDATE products SET stock_qty = ?, is_available = ? WHERE id = ?");
            $stmt->execute([$stock_qty, $is_available, $id]);

            $status = $is_available ? "available" : "sold out";
            logAction($conn, $_SESSION['user_id'], "update_availability", $p['name'] . ": stock " . $stock_qty . ", " . $status);

            $_SESSION['message'] = $p['name'] . " was updated.";
        } else {
            $_SESSION['message'] = "Product not found.";
        }
    }

    header("Location: staff.php");
    exit;
}

// get all products with their category
$products = $pdo->query("SELECT p.*, c.name AS category_name
                         FROM products p
                         JOIN categories c ON c.id = p.category_id
                         ORDER BY p.id")->fetchAll();

include "includes/header.php";
?>

<section>
  <div class="wrap">
    <h1 class="title">Product availability</h1>

    <?php if (isset($_SESSION['message'])) { ?>
      <p class="message ok"><?php echo htmlspecialchars($_SESSION['message']); ?></p>
      <?php unset($_SESSION['message']); ?>
    <?php } ?>

    <div class="table-wrap">
      <table class="admin-table">
        <tr>
          <th>Picture</th>
          <th>Name</th>
          <th>Category</th>
          <th>Stock</th>
          <th>Status</th>
          <th></th>
        </tr>

        <?php foreach ($products as $row) { ?>
          <tr>
            <!-- each row is its own small form -->
            <form method="post" action="staff.php">
              <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

              <td>
                <?php if (!empty($row['image'])) { ?>
                  <img class="thumb" src="images/products/<?php echo htmlspecialchars($row['image']); ?>" alt="">
                <?php } else { ?>
                  <?php echo $row['emoji']; ?>
                <?php } ?>
              </td>
              <td><?php echo htmlspecialchars($row['name']); ?></td>