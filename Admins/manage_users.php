<?php
include "admin_header.php";

// Delete a user
if (isset($_POST['delete_user'])) {
    $user_id = (int) $_POST['user_id'];

    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);

    header("Location: manage_users.php?msg=" . urlencode("User deleted."));
    exit();
}

// Get all users (password is NOT selected)
$users = mysqli_query($conn, "SELECT user_id, full_name, email, phone, created_at
                              FROM users
                              ORDER BY user_id");
?>

<div class="page-top">
    <h1 class="page-title">Users</h1>
</div>

<div class="table-box">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Registered</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>

        <?php if (mysqli_num_rows($users) > 0) { ?>
            <?php while ($user = mysqli_fetch_assoc($users)) { ?>
            <tr>
                <td><?php echo (int) $user['user_id']; ?></td>
                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars((string) $user['phone']); ?></td>
                <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                <td>
                    <form method="POST" action="manage_users.php"
                          onsubmit="return confirm('Are you sure you want to delete this user? Their bookings will also be deleted.');">
                        <input type="hidden" name="user_id" value="<?php echo (int) $user['user_id']; ?>">
                        <button type="submit" name="delete_user" class="btn btn-dark btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
            <?php } ?>
        <?php } else { ?>
            <tr><td colspan="6" class="text-center text-muted">No users found.</td></tr>
        <?php } ?>

        </tbody>
    </table>
</div>

</div><!-- end .page-content -->

<?php $base = "../"; include "../Footer.php"; ?>
</body>
</html>