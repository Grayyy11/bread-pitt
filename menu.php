<?php
// shows products from the database
$pageTitle = "Menu";
include "includes/db.php";
include "includes/header.php";

// get the category names
$categories = ["All"];
$catResult = $conn->query("SELECT name FROM categories ORDER BY id");
while ($c = $catResult->fetch_assoc()) {
    $categories[] = $c['name'];
}

// get the chosen category 
$selected = isset($_GET['category']) ? $_GET['category'] : "All";
if (!in_array($selected, $categories)) {
    $selected = "All";
}
$search = isset($_GET['search']) ? trim($_GET['search']) : "";

// select the products and their category name
$sql = "SELECT p.*, c.name AS category_name
        FROM products p
        JOIN categories c ON c.id = p.category_id
        WHERE 1 = 1";
$types = "";
$params = [];

// filter by category
if ($selected != "All") {
    $sql .= " AND c.name = ?";
    $types .= "s";
    $params[] = $selected;
}

// filter by search word
if ($search != "") {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $like = "%" . $search . "%";
    $types .= "ss";
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY p.id";

// run the query
$stmt = $conn->prepare($sql);
if ($types != "") {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<section>
  <div class="wrap">
    <h1 class="title">Our menu</h1>
        <?php if (isset($_SESSION['message'])) { ?>
      <p class="message ok"><?php echo htmlspecialchars($_SESSION['message']); ?></p>
      <?php unset($_SESSION['message']); ?>
    <?php } ?>

    
    <form class="search-form" method="get" action="menu.php">
      <input type="hidden" name="category" value="<?php echo htmlspecialchars($selected); ?>">
      <input type="text" name="search" placeholder="Search products..."
             value="<?php echo htmlspecialchars($search); ?>">
      <button type="submit" class="btn">Search</button>
    </form>

    
    <div class="filters">
      <?php foreach ($categories as $cat) { ?>
        <a class="filter-btn <?php if ($cat == $selected) echo 'active'; ?>"
           href="menu.php?category=<?php echo urlencode($cat); ?>&search=<?php echo urlencode($search); ?>"><?php echo htmlspecialchars($cat); ?></a>
      <?php } ?>
    </div>

    
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

              <?php if ($row['is_available'] == 0 || $row['stock_qty'] < 1) { ?>
            <p class="sold-out">Sold out</p>
          <?php } elseif (!isLoggedIn()) { ?>
            <a href="login.php" class="btn btn-small">Login to order</a>
          <?php } elseif ($_SESSION['role'] == "customer") { ?>
            <form method="post" action="add_to_cart.php">
              <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
              <button type="submit" class="btn btn-small">Add to cart</button>
            </form>
          <?php } ?>
        </div>
      <?php } ?>
    </div>

    <?php if ($result->num_rows == 0) { ?>
      <p style="text-align:center">No products found.</p>
    <?php } ?>
  </div>
</section>

<?php include "includes/footer.php"; ?>ßßßß