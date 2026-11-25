<?php
// Checkout page: places the order (customer only)
include_once "includes/db.php";
include_once "includes/auth.php";

// only customers can open this page
requireRole(["customer"]);

$pageTitle = "Checkout";
$user_id = $_SESSION['user_id'];
$error = "";
$orderId = 0;

// get the cart items with their product details
$stmt = $conn->prepare("SELECT ci.product_id, ci.quantity, p.name, p.price, p.stock_qty, p.is_available
                        FROM cart_items ci
                        JOIN products p ON p.id = ci.product_id
                        WHERE ci.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// add up the total
$total = 0;
foreach ($items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// run only when the Place order button is clicked
if ($_SERVER['REQUEST_METHOD'] == "POST" && count($items) > 0) {

    // check that every item is still available
    foreach ($items as $item) {
        if ($item['is_available'] == 0 || $item['stock_qty'] < $item['quantity']) {
            $error = $item['name'] . " is not available in that amount anymore. Please update your cart.";
            break;
        }
    }

    if ($error == "") {
        // do all the saving together, or none of it
        $conn->begin_transaction();
        $ok = true;

        // 1. save the order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total) VALUES (?, ?)");
        $stmt->bind_param("id", $user_id, $total);
        $ok = $stmt->execute();
        $newOrderId = $conn->insert_id;

        // 2. save each item and lower the stock
        foreach ($items as $item) {
            if (!$ok) {
                break;
            }

            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisdi", $newOrderId, $item['product_id'], $item['name'], $item['price'], $item['quantity']);
            $ok = $stmt->execute();

            $stmt = $conn->prepare("UPDATE products SET stock_qty = stock_qty - ? WHERE id = ? AND stock_qty >= ?");
            $stmt->bind_param("iii", $item['quantity'], $item['product_id'], $item['quantity']);
            $stmt->execute();
            if ($stmt->affected_rows == 0) {
                $ok = false;
            }
        }

        // 3. empty the cart
        if ($ok) {
            $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $ok = $stmt->execute();
        }

        if ($ok) {
            $conn->commit();
            $orderId = $newOrderId;
            logAction($conn, $user_id, "place_order", "Placed order #" . $orderId . " (total " . number_format($total, 2) . ")");
        } else {
            $conn->rollback();
            $error = "Something went wrong. Your order was not placed.";
        }
    }
}

include "includes/header.php";
?>

<section>
  <div class="wrap">
    <h1 class="title">Checkout</h1>

    <?php if ($orderId > 0) { ?>
      <!-- order placed -->
      <p class="message ok">Thank you! Your order #<?php echo $orderId; ?> was placed.</p>
      <p style="text-align:center"><a href="menu.php" class="btn">Back to the menu</a></p>

    <?php } elseif (count($items) == 0) { ?>
      <p style="text-align:center">Your cart is empty.</p>
      <p style="text-align:center"><a href="menu.php" class="btn">Go to the menu</a></p>

    <?php } else { ?>

      <?php if ($error != "") { ?>
        <p class="message error"><?php echo htmlspecialchars($error); ?></p>
      <?php } ?>

      <!-- order summary -->
      <div class="table-wrap">
        <table class="admin-table">
          <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
          </tr>
          <?php foreach ($items as $item) { ?>
            <tr>
              <td><?php echo htmlspecialchars($item['name']); ?></td>
              <td>&#8369;<?php echo number_format($item['price'], 2); ?></td>
              <td><?php echo $item['quantity']; ?></td>
              <td>&#8369;<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
            </tr>
          <?php } ?>
        </table>
      </div>

      <p class="cart-total">Total: &#8369;<?php echo number_format($total, 2); ?></p>

      <form method="post" action="checkout.php" style="max-width:none; text-align:right">
        <a href="cart.php" style="margin-right:15px">Back to cart</a>
        <button type="submit" class="btn">Place order</button>
      </form>

    <?php } ?>
  </div>
</section>

<?php include "includes/footer.php"; ?>