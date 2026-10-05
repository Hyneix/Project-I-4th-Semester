<?php
// Create an admin account.
// - No admins yet: anyone can create the first one (it becomes Super Admin).
// - After that: only a logged-in Super Admin can add admins and choose their role.
session_start();
include "../dbconnection.php";

$result = mysqli_query($conn, "SELECT COUNT(*) FROM admins");
$is_first_admin = (mysqli_fetch_row($result)[0] == 0);

if (!$is_first_admin) {
    // Must be logged in
    if (!isset($_SESSION['admin_id'])) {
        header("Location: admin_login.php");
        exit();
    }

    // Must be a Super Admin
    $admin_id = (int) $_SESSION['admin_id'];
    $sql = "SELECT admin_roles.role_name
            FROM admins
            JOIN admin_roles ON admins.role_id = admin_roles.role_id
            WHERE admins.admin_id = $admin_id";
    $me = mysqli_fetch_assoc(mysqli_query($conn, $sql));

    if (!$me || $me['role_name'] != 'Super Admin') {
        header("Location: admin_dashboard.php");
        exit();
    }
}

// All roles (for the dropdown)
$roles = mysqli_query($conn, "SELECT role_id, role_name FROM admin_roles ORDER BY role_id");

$error = "";
$success = "";
$full_name = "";
$username = "";
$email = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($full_name == "" || $username == "" || $email == "" || $password == "") {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password != $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT admin_id FROM admins WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = "That username or email is already used.";
        }
    }

    $role_id = 0;
    if ($error == "") {
        if ($is_first_admin) {
            $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT role_id FROM admin_roles WHERE role_name = 'Super Admin'"));
            if ($row) {
                $role_id = $row['role_id'];
            } else {
                $error = "Role 'Super Admin' not found. Run admin_setup.sql first.";
            }
        } else {
            $role_id = (int) $_POST['role_id'];
            $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT role_id FROM admin_roles WHERE role_id = $role_id"));
            if (!$row) {
                $error = "Please choose a valid role.";
            }
        }
    }

    if ($error == "") {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare($conn, "INSERT INTO admins (username, password, full_name, email, role_id)
                                       VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssssi", $username, $hashed_password, $full_name, $email, $role_id);

        if (mysqli_stmt_execute($stmt)) {
            $success = "Admin account created.";
            $full_name = "";
            $username = "";
            $email = "";
        } else {
            $error = "Could not create the account. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Register - Room Booking System</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="container">
    <div class="auth-box box">

        <h1 class="page-title mb-3"><?php echo $is_first_admin ? 'Create First Admin' : 'Add Admin'; ?></h1>

        <?php if ($error != "") { ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <?php if ($success != "") { ?>
            <div class="alert alert-secondary"><?php echo htmlspecialchars($success); ?></div>
        <?php } ?>

        <form method="POST" action="admin_register.php">
            <div class="mb-3">
                <label class="form-label" for="full_name">Full Name</label>
                <input type="text" class="form-control" id="full_name" name="full_name"
                       value="<?php echo htmlspecialchars($full_name); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username"
                       value="<?php echo htmlspecialchars($username); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email"
                       value="<?php echo htmlspecialchars($email); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="confirm_password">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>

            <?php if (!$is_first_admin) { ?>
                <div class="mb-3">
                    <label class="form-label" for="role_id">Role</label>
                    <select class="form-select" id="role_id" name="role_id">
                        <?php while ($role = mysqli_fetch_assoc($roles)) { ?>
                            <option value="<?php echo (int) $role['role_id']; ?>">
                                <?php echo htmlspecialchars($role['role_name']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            <?php } ?>

            <button type="submit" class="btn btn-dark w-100">Register</button>
        </form>

        <p class="mt-3 mb-0">
            <?php if ($is_first_admin) { ?>
                <a href="admin_login.php" class="text-dark">Go to login</a>
            <?php } else { ?>
                <a href="manage_admins.php" class="text-dark">&larr; Back to Admins</a>
            <?php } ?>
        </p>

    </div>
</div>

<?php $base = "../"; include "../Footer.php"; ?>
</body>
</html>