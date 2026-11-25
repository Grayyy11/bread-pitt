<?php
// My orders page: shows the customer's past orders
include_once "includes/db.php";
include_once "includes/auth.php";

// only customers can open this page
requireRole(["customer"]);

$pageTitle = "My Orders";
$user_id = $_SESSION['user_id'];

// get this customer's orders, newest first
$stmt = $conn->prepare("SELECT id, total, status, created_at
                        FROM orders
                        WHERE user_id = ?
                        ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();

include "includes/header.php";
?>

<section>
  <div class="wrap">
    <h1 class="title">My orders</h1>

    <?php if ($orders->num_rows == 0) { ?>
      <p style="text-align:center">You have no orders yet.</p>
      <p style="text-align:center"><a href="menu.php" class="btn">Go to the menu</a></p>
    <?php } ?>

    <?php while ($order = $orders->fetch_assoc()) { ?>
      <div class="card order-box">

        <p class="order-head">
          <strong>Order #<?php echo $order['id']; ?></strong>
          <span><?php echo htmlspecialchars($order['created_at']); ?></span>
          <span>Status: <?php echo htmlspecialchars($order['status']); ?></span>
        </p>

        <?php
          // get the items of this order
          $stmt = $conn->prepare("SELECT product_name, price, quantity FROM order_items WHERE order_id = ?");
          $stmt->bind_param("i", $order['id']);
          $stmt->execute();
          $items = $stmt->get_result();
        ?>

        <div class="table-wrap">
          <table class="admin-table">
            <tr>
              <th>Product</th>
              <th>Price</th>
              <th>Quantity</th>
              <th>Subtotal</th>
            </tr>
            <?php while ($item = $items->fetch_assoc()) { ?>
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