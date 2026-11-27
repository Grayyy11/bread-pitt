<?php
// My orders page: shows the customer's past orders
include_once "includes/db.php";
include_once "includes/auth.php";

// only customers can open this page
requireRole(["customer"]);

$pageTitle = "My Orders";
$user_id = $_SESSION['user_id'];

// get this customer's orders, newest first
$stmt = $pdo->prepare("SELECT id, total, status, created_at
                       FROM orders
                       WHERE user_id = ?
                       ORDER BY id DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

// prepare the query for the items of an order (used once per order)
$itemStmt = $pdo->prepare("SELECT product_name, price, quantity FROM order_items WHERE order_id = ?");

include "includes/header.php";
?>

<section>
  <div class="wrap">
    <h1 class="title">My orders</h1>

    <?php if (count($orders) == 0) { ?>
      <p style="text-align:center">You have no orders yet.</p>
      <p style="text-align:center"><a href="menu.php" class="btn">Go to the menu</a></p>
    <?php } ?>

    <?php foreach ($orders as $order) { ?>
      <div class="card order-box">

        <p class="order-head">
          <strong>Order #<?php echo $order['id']; ?></strong>
          <span><?php echo htmlspecialchars($order['created_at']); ?></span>
          <span>Status: <?php echo htmlspecialchars($order['status']); ?></span>
        </p>

        <?php
          // get the items of this order
          $itemStmt->execute([$order['id']]);
          $items = $itemStmt->fetchAll();
        ?>

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
                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                <td>&#8369;<?php echo number_format($item['price'], 2); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td>&#8369;<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
              </tr>
            <?php } ?>
          </table>
        </div>

        <p class="cart-total">Total: &#8369;<?php echo number_format($order['total'], 2); ?></p>
      </div>
    <?php } ?>
  </div>
</section>

<?php include "includes/footer.php"; ?>