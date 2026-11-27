<?php
// Audit log page (admin only)
include_once "includes/db.php";
include_once "includes/auth.php";

// only admins can open this page
requireRole(["admin"]);

$pageTitle = "Audit Logs";

// get the list of actions for the filter
$actions = $pdo->query("SELECT DISTINCT action FROM audit_logs ORDER BY action")->fetchAll(PDO::FETCH_COLUMN);

// get the chosen action from the address
$selected = $_GET['action'] ?? "";
if ($selected != "" && !in_array($selected, $actions)) {
    $selected = "";
}

// get the latest 100 log entries with the user name
$sql = "SELECT l.*, u.full_name
        FROM audit_logs l
        LEFT JOIN users u ON u.id = l.user_id";
$params = [];

if ($selected != "") {
    $sql .= " WHERE l.action = ?";
    $params[] = $selected;
}

$sql .= " ORDER BY l.id DESC LIMIT 100";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

include "includes/header.php";
?>

<section>
  <div class="wrap">
    <h1 class="title">Audit logs</h1>

    <!-- filter by action -->
    <form class="search-form" method="get" action="audit.php">
      <select name="action">
        <option value="">All actions</option>
        <?php foreach ($actions as $a) { ?>
          <option value="<?php echo htmlspecialchars($a); ?>" <?php if ($a == $selected) echo "selected"; ?>>
            <?php echo htmlspecialchars($a); ?>
          </option>
        <?php } ?>
      </select>
      <button type="submit" class="btn">Filter</button>
    </form>

    <div class="table-wrap">
      <table class="admin-table">
        <tr>
          <th>Date and time</th>
          <th>User</th>
          <th>Action</th>
          <th>Details</th>
        </tr>

        <?php foreach ($logs as $row) { ?>
          <tr>
            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
            <td><?php echo $row['full_name'] ? htmlspecialchars($row['full_name']) : "(unknown)"; ?></td>
            <td><?php echo htmlspecialchars($row['action']); ?></td>
            <td><?php echo htmlspecialchars($row['details']); ?></td>
          </tr>
        <?php } ?>
      </table>
    </div>

    <?php if (count($logs) == 0) { ?>
      <p style="text-align:center">No log entries found.</p>
    <?php } ?>
  </div>
</section>

<?php include "includes/footer.php"; ?>