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
$stmt = $pdo->prepare("SELECT ci.product_id, ci.quantity, p.name, p.price, p.stock_qty, p.is_available
                       FROM cart_items ci
                       JOIN products p ON p.id = ci.product_id
                       WHERE ci.user_id = ?");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll();

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
        try {
            // save everything together, or nothing
            $pdo->beginTransaction();

            // 1. save the order
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total) VALUES (?, ?)");
            $stmt->execute([$user_id, $total]);
            $newOrderId = $pdo->lastInsertId();

            // 2. save each item and lower the stock
            $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity)
                                       VALUES (?, ?, ?, ?, ?)");
            $stockStmt = $pdo->prepare("UPDATE products
                                        SET stock_qty = stock_qty - ?
                                        WHERE id = ? AND stock_qty >= ?");

            foreach ($items as $item) {
                $itemStmt->execute([$newOrderId, $item['product_id'], $item['name'], $item['price'], $item['quantity']]);

                $stockStmt->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
                if ($stockStmt->rowCount() == 0) {
                    throw new Exception("Not enough stock for " . $item['name']);
                }
            }

            // 3. empty the cart
            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $stmt->execute([$user_id]);

            $pdo->commit();
            $orderId = $newOrderId;

            logAction($conn, $user_id, "place_order", "Placed order #" . $orderId . " (total " . number_format($total, 2) . ")");

        } catch (Exception $e) {
            // something failed: undo everything
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
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
      <p class="message ok">Thank you! Your order #<?php echo $orderId; ?> was placed. We will mark it as ready when you can pick it up.</p>
      <p style="text-align:center"><a href="menu.php" class="btn">Back to the menu</a></p>

    <?php } elseif (count($items) == 0) { ?>
      <p style="text-align:center">Your cart is empty.</p>
      <p style="text-align:center"><a href="menu.php" class="btn">Go to the menu</a></p>

    <?php } else { ?>

      <?php if ($error != "") { ?>
        <p class="message error"><?php echo htmlspecialchars($error); ?></p>
      <?php } ?>

      <p class="message ok">Pickup only. You will pay at the counter when you collect your order.</p>

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