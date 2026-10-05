<?php

ob_start();
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include "../dbconnection.php";

$admin_id = (int) $_SESSION['admin_id'];
$sql = "SELECT admins.role_id, admin_roles.role_name
        FROM admins
        JOIN admin_roles ON admins.role_id = admin_roles.role_id
        WHERE admins.admin_id = $admin_id";
$result = mysqli_query($conn, $sql);
$admin = mysqli_fetch_assoc($result);

if (!$admin) {
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_name']);
    unset($_SESSION['role_id']);
    header("Location: admin_login.php");
    exit();
}

$_SESSION['role_id'] = $admin['role_id'];
$is_super_admin = ($admin['role_name'] == 'Super Admin');

$current_page = basename($_SERVER['PHP_SELF']);
$first_letter = mb_strtoupper(mb_substr($_SESSION['admin_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Room Booking System</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="admin.css">
</head>
<body>

<header class="site-header">

    <a href="admin_dashboard.php" class="admin-label">ADMIN</a>

    <nav class="nav-section">
        <a href="admin_dashboard.php" class="<?php if ($current_page == 'admin_dashboard.php') echo 'active'; ?>">Dashboard</a>
        <a href="manage_rooms.php" class="<?php if ($current_page == 'manage_rooms.php' || $current_page == 'room_form.php') echo 'active'; ?>">Rooms</a>
        <a href="manage_bookings.php" class="<?php if ($current_page == 'manage_bookings.php') echo 'active'; ?>">Bookings</a>
        <a href="manage_users.php" class="<?php if ($current_page == 'manage_users.php') echo 'active'; ?>">Users</a>
        <a href="manage_messages.php" class="<?php if ($current_page == 'manage_messages.php') echo 'active'; ?>">Messages</a>

        <?php if ($is_super_admin) { ?>
            <a href="manage_admins.php" class="<?php if ($current_page == 'manage_admins.php') echo 'active'; ?>">Admins</a>
        <?php } ?>
    </nav>

    <div class="right-section">
        <div class="user-icon"><?php echo htmlspecialchars($first_letter); ?></div>
        <a href="admin_logout.php">Logout</a>
    </div>

</header>

<div class="container page-content">

<?php if (isset($_GET['msg'])) { ?>
    <div class="alert alert-secondary"><?php echo htmlspecialchars($_GET['msg']); ?></div>
<?php } ?>

<?php if (isset($_GET['error'])) { ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
<?php } ?>