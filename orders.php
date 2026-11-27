<?php
// Orders page: staff and admin see all orders
include_once "includes/db.php";
include_once "includes/auth.php";

// only staff and admin can open this page
requireRole(["staff", "admin"]);

$pageTitle = "Orders";

// the statuses an order can have
$statuses = ["pending", "preparing", "ready", "completed", "cancelled"];

// run only when an Update button is clicked
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $id = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? "";

    // only accept a status from our list
    if (in_array($status, $statuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);

        logAction($conn, $_SESSION['user_id'], "update_order", "Order #" . $id . " set to " . $status);
        $_SESSION['message'] = "Order #" . $id . " is now " . $status . ".";
    }

    header("Location: orders.php");
    exit;
}

// get the chosen status filter
$selected = $_GET['status'] ?? "";
if ($selected != "" && !in_array($selected, $statuses)) {
    $selected = "";
}

// get the orders with the customer name
$sql = "SELECT o.*, u.full_name
        FROM orders o
        JOIN users u ON u.id = o.user_id";
$params = [];

if ($selected != "") {
    $sql .= " WHERE o.status = ?";
    $params[] = $selected;
}

$sql .= " ORDER BY o.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// prepare the query for the items of an order (used once per order)
$itemStmt = $pdo->prepare("SELECT product_name, price, quantity FROM order_items WHERE order_id = ?");

include "includes/header.php";
?>

<section>
  <div class="wrap">
    <h1 class="title">All orders</h1>

    <?php if (isset($_SESSION['message'])) { ?>
      <p class="message ok"><?php echo htmlspecialchars($_SESSION['message']); ?></p>
      <?php unset($_SESSION['message']); ?>
    <?php } ?>

    <!-- filter by status -->
    <form class="search-form" method="get" action="orders.php">
      <select name="status">
        <option value="">All statuses</option>
        <?php foreach ($statuses as $s) { ?>
          <option value="<?php echo $s; ?>" <?php if ($s == $selected) echo "selected"; ?>>
            <?php echo ucfirst($s); ?>
          </option>
        <?php } ?>
      </select>
      <button type="submit" class="btn">Filter</button>
    </form>

    <?php if (count($orders) == 0) { ?>
      <p style="text-align:center">No orders found.</p>
    <?php } ?>

    <?php foreach ($orders as $order) { ?>
      <div class="card order-box">

        <p class="order-head">
          <strong>Order #<?php echo $order['id']; ?></strong>
          <span><?php echo htmlspecialchars($order['full_name']); ?></span>
          <span><?php echo htmlspecialchars($order['created_at']); ?></span>
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

        <!-- change the status -->
        <form method="post" action="orders.php" class="inline-form">
          <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
          <select name="status" class="small-input">
            <?php foreach ($statuses as $s) { ?>
              <option value="<?php echo $s; ?>" <?php if ($s == $order['status']) echo "selected"; ?>>
                <?php echo ucfirst($s); ?>
              </option>
            <?php } ?>
          </select>
          <button type="submit" class="btn btn-small">Update</button>
        </form>
      </div>
    <?php } ?>
  </div>
</section>

<?php include "includes/footer.php"; ?>