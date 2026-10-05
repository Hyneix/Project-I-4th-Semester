<?php
session_start();
include "../dbconnection.php";

if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

// Show the "create first admin" link only when there are no admins yet
$result = mysqli_query($conn, "SELECT COUNT(*) FROM admins");
$is_first_admin = (mysqli_fetch_row($result)[0] == 0);

$error = "";
$username = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT admin_id, password, full_name, role_id FROM admins WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_name'] = $admin['full_name'];
        $_SESSION['role_id'] = $admin['role_id'];
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Wrong username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login - Room Booking System</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="container">
    <div class="auth-box box">

        <h1 class="page-title mb-3">Admin Login</h1>

        <?php if ($error != "") { ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <form method="POST" action="admin_login.php">
            <div class="mb-3">
                <label class="form-label" for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username"
                       value="<?php echo htmlspecialchars($username); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-dark w-100">Login</button>
        </form>

        <?php if ($is_first_admin) { ?>
            <p class="mt-3 mb-0"><a href="admin_register.php" class="text-dark">Create first admin</a></p>
        <?php } ?>

    </div>
</div>

<?php $base = "../"; include "../Footer.php"; ?>
</body>
</html>