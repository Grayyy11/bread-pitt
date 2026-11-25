<?php
// Cart page: shows the customer's cart
include_once "includes/db.php";
include_once "includes/auth.php";

// only customers can open this page
requireRole(["customer"]);

$pageTitle = "My Cart";
$user_id = $_SESSION['user_id'];

// remove an item when the remove button is clicked
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['remove_id'])) {
    $remove_id = (int)$_POST['remove_id'];

    $stmt = $conn->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $remove_id, $user_id);
    $stmt->execute();

    logAction($conn, $user_id, "remove_from_cart", "Removed a cart item");
    header("Location: cart.php");
    exit;
}

// get the cart items with their product details
$stmt = $conn->prepare("SELECT ci.id, ci.quantity, p.name, p.price, p.image, p.emoji
                        FROM cart_items ci
                        JOIN products p ON p.id = ci.product_id
                        WHERE ci.user_id = ?
                        ORDER BY ci.added_at");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;

include "includes/header.php";
?>

<section>
  <div class="wrap">
    <h1 class="title">My cart</h1>

    <?php if ($result->num_rows == 0) { ?>
      <p style="text-align:center">Your cart is empty.</p>
      <p style="text-align:center"><a href="menu.php" class="btn">Go to the menu</a></p>
    <?php } else { ?>

      <div class="table-wrap">
        <table class="admin-table">
          <tr>
            <th>Picture</th>
            <th>Product</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
            <th></th>
          </tr>

          <?php while ($row = $result->fetch_assoc()) { ?>
            <?php
              // price times quantity
              $subtotal = $row['price'] * $row['quantity'];
              $total += $subtotal;
            ?>
            <tr>
              <td>
                <?php if (!empty($row['image'])) { ?>
                  <img class="thumb" src="images/products/<?php echo htmlspecialchars($row['image']); ?>" alt="">
                <?php } else { ?>
                  <?php echo $row['emoji']; ?>
                <?php } ?>
              </td>
              <td><?php echo htmlspecialchars($row['name']); ?></td>
              <td>&#8369;<?php echo number_format($row['price'], 2); ?></td>
              <td><?php echo $row['quantity']; ?></td>
              <td>&#8369;<?php echo number_format($subtotal, 2); ?></td>
              <td>
                <form method="post" action="cart.php">
                  <input type="hidden" name="remove_id" value="<?php echo $row['id']; ?>">
                  <button type="submit" class="btn btn-danger btn-small">Remove</button>
                </form>
              </td>
            </tr>
          <?php } ?>
        </table>
      </div>

      <p class="cart-total">Total: &#8369;<?php echo number_format($total, 2); ?></p>
      <p style="text-align:right"><a href="checkout.php" class="btn">Checkout</a></p>
    <?php } ?>
  </div>
</section>

<?php include "includes/footer.php"; ?>