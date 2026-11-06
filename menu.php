<?php
// menu.php - Menu page (products come from MySQL)
$pageTitle = "Menu";
include "includes/db.php";
include "includes/header.php";

// Which category was clicked? (menu.php?category=Bread)
$categories = ["All", "Bread", "Pastry", "Cake"];
$selected = isset($_GET['category']) ? $_GET['category'] : "All";

// Only allow categories from our list
if (!in_array($selected, $categories)) {
    $selected = "All";
}

// Get the products from the database
if ($selected == "All") {
    $result = $conn->query("SELECT * FROM products ORDER BY id");
} else {
    $stmt = $conn->prepare("SELECT * FROM products WHERE category = ? ORDER BY id");
    $stmt->bind_param("s", $selected);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<section>
  <div class="wrap">
    <h1 class="title">Our menu</h1>

    <!-- Filter buttons -->
    <div class="filters">
      <?php foreach ($categories as $cat) { ?>
        <a class="filter-btn <?php if ($cat == $selected) echo 'active'; ?>"
           href="menu.php?category=<?php echo $cat; ?>"><?php echo $cat; ?></a>
      <?php } ?>
    </div>

    <!-- Product cards -->
    <div class="grid">
      <?php while ($row = $result->fetch_assoc()) { ?>
        <div class="card product">

          <?php if (!empty($row['image'])) { ?>
            <img class="product-img"
                 src="images/products/<?php echo htmlspecialchars($row['image']); ?>"
                 alt="<?php echo htmlspecialchars($row['name']); ?>">
          <?php } else { ?>
            <div class="emoji"><?php echo $row['emoji']; ?></div>
          <?php } ?>

          <h3><?php echo htmlspecialchars($row['name']); ?></h3>
          <p><?php echo htmlspecialchars($row['description']); ?></p>
          <p class="price">&#8369;<?php echo number_format($row['price'], 2); ?></p>
        </div>
      <?php } ?>
    </div>

    <?php if ($result->num_rows == 0) { ?>
      <p style="text-align:center">No products in this category yet.</p>
    <?php } ?>
  </div>
</section>

<?php include "includes/footer.php"; ?>