<?php
// Admin page: lists all products (admin only)
include_once "includes/db.php";
include_once "includes/auth.php";

// only admins can open this page
requireRole(["admin"]);

$pageTitle = "Admin";

// get the products with their category name
$products = $pdo->query("SELECT p.*, c.name AS category_name
                         FROM products p
                         JOIN categories c ON c.id = p.category_id
                         ORDER BY p.id")->fetchAll();

include "includes/header.php";
?>

<section>
  <div class="wrap">
    <h1 class="title">Manage products</h1>

    <p style="text-align:center">
      <a href="add.php" class="btn">+ Add new product</a>
    </p>

    <div class="table-wrap">
      <table class="admin-table">
        <tr>
          <th>Picture</th>
          <th>Name</th>
          <th>Category</th>
          <th>Price</th>
          <th>Available</th>
          <th>Actions</th>
        </tr>

        <?php foreach ($products as $row) { ?>
          <tr>
            <td>
              <?php if (!empty($row['image'])) { ?>
                <img class="thumb" src="images/products/<?php echo htmlspecialchars($row['image']); ?>" alt="">
              <?php } else { ?>
                <?php echo $row['emoji']; ?>
              <?php } ?>
            </td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['category_name']); ?></td>
            <td>&#8369;<?php echo number_format($row['price'], 2); ?></td>
            <td><?php echo $row['is_available'] ? "Yes" : "No"; ?></td>
            <td>
              <a href="edit.php?id=<?php echo $row['id']; ?>">Edit</a> |
              <a href="delete.php?id=<?php echo $row['id']; ?>">Delete</a>
            </td>
          </tr>
        <?php } ?>
      </table>
    </div>
  </div>
</section>

<?php include "includes/footer.php"; ?>