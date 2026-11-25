<?php
// User management page (admin only)
include_once "includes/db.php";
include_once "includes/auth.php";

// only admins can open this page
requireRole(["admin"]);

$pageTitle = "Users";

// run only when a button is clicked
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $id = (int)$_POST['id'];

    // an admin cannot change their own account
    if ($id == $_SESSION['user_id']) {
        $_SESSION['message'] = "You cannot change your own account.";
        header("Location: users.php");
        exit;
    }

    // find the user for the audit log
    $stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $u = $stmt->get_result()->fetch_assoc();

    if ($u) {
        if ($_POST['action'] == "role") {
            // change the role
            $role_id = (int)$_POST['role_id'];
            $stmt = $conn->prepare("UPDATE users SET role_id = ? WHERE id = ?");
            $stmt->bind_param("ii", $role_id, $id);
            $stmt->execute();

            logAction($conn, $_SESSION['user_id'], "change_role", "Changed role of " . $u['full_name']);
            $_SESSION['message'] = "Role updated for " . $u['full_name'] . ".";

        } elseif ($_POST['action'] == "toggle") {
            // switch between active and deactivated
            $stmt = $conn->prepare("UPDATE users SET is_active = 1 - is_active WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            logAction($conn, $_SESSION['user_id'], "toggle_user", "Changed status of " . $u['full_name']);
            $_SESSION['message'] = "Status updated for " . $u['full_name'] . ".";
        }
    }

    header("Location: users.php");
    exit;
}

// get the roles for the dropdown
$roles = [];
$roleResult = $conn->query("SELECT id, name FROM roles ORDER BY id");
while ($r = $roleResult->fetch_assoc()) {
    $roles[] = $r;
}

// get all users with their role name
$result = $conn->query("SELECT u.*, r.name AS role_name
                        FROM users u
                        JOIN roles r ON r.id = u.role_id
                        ORDER BY u.id");

include "includes/header.php";
?>

<section>
  <div class="wrap">
    <h1 class="title">Manage users</h1>

    <?php if (isset($_SESSION['message'])) { ?>
      <p class="message ok"><?php echo htmlspecialchars($_SESSION['message']); ?></p>
      <?php unset($_SESSION['message']); ?>
    <?php } ?>

    <div class="table-wrap">
      <table class="admin-table">
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()) { ?>
          <tr>
            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>

            <?php if ($row['id'] == $_SESSION['user_id']) { ?>
              <!-- your own account: no buttons -->
              <td><?php echo htmlspecialchars($row['role_name']); ?></td>
              <td>Active</td>
              <td>(you)</td>

            <?php } else { ?>
              <td>
                <form method="post" action="users.php" class="inline-form">
                  <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                  <input type="hidden" name="action" value="role">
                  <select name="role_id" class="small-input">
                    <?php foreach ($roles as $r) { ?>
                      <option value="<?php echo $r['id']; ?>" <?php if ($r['id'] == $row['role_id']) echo "selected"; ?>>
                        <?php echo htmlspecialchars($r['name']); ?>
                      </option>
                    <?php } ?>
                  </select>
                  <button type="submit" class="btn btn-small">Save</button>
                </form>
              </td>
              <td><?php echo $row['is_active'] ? "Active" : "Deactivated"; ?></td>
              <td>
                <form method="post" action="users.php">
                  <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                  <input type="hidden" name="action" value="toggle">
                  <button type="submit" class="btn btn-small <?php if ($row['is_active']) echo 'btn-danger'; ?>">
                    <?php echo $row['is_active'] ? "Deactivate" : "Activate"; ?>
                  </button>
                </form>
              </td>
            <?php } ?>
          </tr>
        <?php } ?>
      </table>
    </div>
  </div>
</section>

<?php include "includes/footer.php"; ?>