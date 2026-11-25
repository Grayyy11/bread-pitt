<?php
// Delete product page (admin only)
include_once "includes/db.php";
include_once "includes/auth.php";

// only admins can open this page
requireRole(["admin"]);

$pageTitle = "Delete Product";

// get the product id from the address or the form
$id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;

// find the product
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    die("Product not found.");
}

// delete only after the confirm button is clicked
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // delete the picture file if no other product uses it
    if (!empty($product['image'])) {
        $stmt = $conn->prepare("SELECT id FROM products WHERE image = ?");
        $stmt->bind_param("s", $product['image']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows == 0) {
            $file = "images/products/" . basename($product['image']);
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    // save in the audit log
    logAction($conn, $_SESSION['user_id'], "delete_product", "Deleted product: " . $product['name']);

    header("Location: admin.php");
    exit;
}

include "includes/header.php";
?>

<section>
  <div class="wrap">
    <h1 class="title">Delete product</h1>

    <div class="card" style="max-width:500px; margin:0 auto; text-align:center">
      <?php if (!empty($product['image'])) { ?>
        <img class="product-img" src="images/products/<?php echo htmlspecialchars($product['image']); ?>" alt="">
      <?php } ?>

      <p>Are you sure you want to delete <strong><?php echo htmlspecialchars($product['name']); ?></strong>?</p>
      <p>This also removes it from all carts and cannot be undone.</p>

      <form method="post" action="delete.php">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <button type="submit" class="btn btn-danger">Yes, delete</button>
        <a href="admin.php" style="margin-left:15px">Cancel</a>
      </form>
    </div>
  </div>
</section>

<?php include "includes/footer.php"; ?>