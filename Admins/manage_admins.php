<?php
include "admin_header.php";

if (!$is_super_admin) {
    ?>
    <div class="alert alert-danger">Unauthorized. Only a Super Admin can open this page.</div>
    </div>
    <?php $base = "../"; include "../Footer.php"; ?>
    </body>
    </html>
    <?php
    exit();
}

$roles = [];
$result = mysqli_query($conn, "SELECT role_id, role_name FROM admin_roles ORDER BY role_id");
while ($role = mysqli_fetch_assoc($result)) {
    $roles[] = $role;
}

if (isset($_POST['change_role'])) {

    $change_id = (int) $_POST['admin_id'];
    $role_id = (int) $_POST['role_id'];
    $error = "";

    $role_exists = false;
    foreach ($roles as $role) {
        if ($role['role_id'] == $role_id) {
            $role_exists = true;
        }
    }

    if ($change_id == $_SESSION['admin_id']) {
        $error = "You cannot change your own role.";   
    } elseif (!$role_exists) {
        $error = "Invalid role.";
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE admins SET role_id = ? WHERE admin_id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $role_id, $change_id);
        mysqli_stmt_execute($stmt);
    }

    if ($error != "") {
        header("Location: manage_admins.php?error=" . urlencode($error));
    } else {
        header("Location: manage_admins.php?msg=" . urlencode("Role updated."));
    }
    exit();
}

$admins = mysqli_query($conn, "SELECT admins.admin_id, admins.full_name, admins.username, admins.email,
                                      admins.role_id, admin_roles.role_name
                               FROM admins
                               JOIN admin_roles ON admins.role_id = admin_roles.role_id
                               ORDER BY admins.admin_id");
?>

<div class="page-top">
    <h1 class="page-title">Admins</h1>
    <a href="admin_register.php" class="btn btn-dark">+ Add Admin</a>
</div>

<div class="table-box">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Change Role</th>
            </tr>
        </thead>
        <tbody>

        <?php while ($a = mysqli_fetch_assoc($admins)) { ?>
        <tr>
            <td><?php echo (int) $a['admin_id']; ?></td>
            <td><?php echo htmlspecialchars($a['full_name']); ?></td>
            <td><?php echo htmlspecialchars($a['username']); ?></td>
            <td><?php echo htmlspecialchars($a['email']); ?></td>
            <td><?php echo htmlspecialchars($a['role_name']); ?></td>
            <td>
                <?php if ($a['admin_id'] == $_SESSION['admin_id']) { ?>
                    <span class="text-muted">You</span>
                <?php } else { ?>
                    <form method="POST" action="manage_admins.php" class="d-flex gap-2">
                        <input type="hidden" name="admin_id" value="<?php echo (int) $a['admin_id']; ?>">
                        <select name="role_id" class="form-select form-select-sm" style="max-width:160px;">
                            <?php foreach ($roles as $role) { ?>
                                <option value="<?php echo (int) $role['role_id']; ?>"
                                    <?php if ($a['role_id'] == $role['role_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($role['role_name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                        <button type="submit" name="change_role" class="btn btn-dark btn-sm">Save</button>
                    </form>
                <?php } ?>
            </td>
        </tr>
        <?php } ?>

        </tbody>
    </table>
</div>

</div>

<?php $base = "../"; include "../Footer.php"; ?>
</body>
</html>