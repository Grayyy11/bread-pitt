<?php
// Add product page (admin only!)
include_once "includes/db.php";
include_once "includes/auth.php";

// only admins can open this page
requireRole(["admin"]);

$pageTitle = "Add Product";
$errors = [];

// default values for the form
$name = "";
$description = "";
$price = "";
$stock_qty = 20;
$category_id = "";

// get the categories for the dropdown
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY id")->fetchAll();

// run only when the form is submitted
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $name = trim($_POST['name'] ?? "");
    $description = trim($_POST['description'] ?? "");
    $price = $_POST['price'] ?? "";
    $stock_qty = $_POST['stock_qty'] ?? "";
    $category_id = $_POST['category_id'] ?? "";
    $is_available = isset($_POST['is_available']) ? 1 : 0;

    // check the form
    if ($name == "") {
        $errors[] = "Please enter a product name.";
    }
    if ($category_id == "") {
        $errors[] = "Please choose a category.";
    }
    if (!is_numeric($price) || $price < 0) {
        $errors[] = "Price must be a number.";
    }
    if (!ctype_digit((string)$stock_qty)) {
        $errors[] = "Stock must be a whole number.";
    }

    // check the picture (optional)
    $imageName = null;
    if (!empty($_FILES['image']['name'])) {
        $allowed = ["jpg", "jpeg", "png", "webp"];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if ($_FILES['image']['error'] != 0) {
            $errors[] = "The picture could not be uploaded.";
        } elseif (!in_array($ext, $allowed)) {
            $errors[] = "Picture must be jpg, png, or webp.";
        } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $errors[] = "Picture must be 2 MB or smaller.";
        } elseif (!getimagesize($_FILES['image']['tmp_name'])) {
            $errors[] = "That file is not a real picture.";
        } else {
            // make a safe file name
            $imageName = time() . "_" . preg_replace("/[^a-z0-9]/", "", strtolower(pathinfo($_FILES['image']['name'], PATHINFO_FILENAME))) . "." . $ext;
        }
    }

    // save the product
    if (empty($errors)) {
        if ($imageName != null) {
            move_uploaded_file($_FILES['image']['tmp_name'], "images/products/" . $imageName);
        }

        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image, category_id, is_available, stock_qty)
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $imageName, $category_id, $is_available, $stock_qty]);

        // save in the audit log
        logAction($conn, $_SESSION['user_id'], "add_product", "Added product: " . $name);

        header("Location: admin.php");
        exit;
    }
}

include "includes/header.php";
?>

<section>
  <div class="wrap">
    <h1 class="title">Add product</h1>

    <?php foreach ($errors as $error) { ?>
      <p class="message error"><?php echo htmlspecialchars($error); ?></p>
    <?php } ?>

    <form method="post" action="add.php" enctype="multipart/form-data">
      <label for="name">Product name</label>
      <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>">

      <label for="category_id">Category</label>
      <select id="category_id" name="category_id">
        <option value="">Choose a category</option>
        <?php foreach ($categories as $c) { ?>
          <option value="<?php echo $c['id']; ?>" <?php if ($c['id'] == $category_id) echo "selected"; ?>>
            <?php echo htmlspecialchars($c['name']); ?>
          </option>
        <?php } ?>
      </select>

      <label for="price">Price</label>
      <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($price); ?>">

      <label for="description">Description</label>
      <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($description); ?></textarea>

      <label for="stock_qty">Stock</label>
      <input type="number" id="stock_qty" name="stock_qty" min="0" value="<?php echo htmlspecialchars($stock_qty); ?>">

      <label for="image">Picture (jpg, png, or webp)</label>
      <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">

      <label class="check-label">
        <input type="checkbox" name="is_available" checked> Available
      </label>

      <button type="submit" class="btn" style="margin-top:15px">Save product</button>
      <a href="admin.php" style="margin-left:15px">Cancel</a>
    </form>
  </div>
</section>

<?php include "includes/footer.php"; ?>