<?php
session_start();
include "dbconnection.php";

$error = "";
$success = "";

if (isset($_GET['reset']) && $_GET['reset'] == "success") {
    $success = "Password reset successful. Please login with your new password.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = trim($_POST['login']);
    $password = $_POST['password'];

    if (empty($login) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT admin_id, username, full_name, email, role_id, password FROM admins WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();

            if (password_verify($password, $row['password'])) {
                $_SESSION['admin_id'] = $row['admin_id'];
                $_SESSION['admin_username'] = $row['username'];
                $_SESSION['admin_full_name'] = $row['full_name'];
                $_SESSION['admin_email'] = $row['email'];
                $_SESSION['admin_role_id'] = $row['role_id'];

                header("Location: admin_dashboard.php");
                exit();
            } else {
                $error = "Invalid username/email or password.";
            }
        } else {
            $error = "Invalid username/email or password.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Room Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: Arial, sans-serif;
        }
        .login-box {
            width: 100%;
            max-width: 400px;
            margin: 80px auto;
            padding: 20px;
        }
        .card {
            border: none;
            padding: 20px;
        }
        h3 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .form-label {
            font-size: 14px;
        }
        p {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }
        .forgot-link {
            text-align: right;
            font-size: 13px;
            margin-top: -10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="login-box">
    <div class="card shadow-sm">
        <h3>Room Booking System</h3>
        <h5 class="text-center mb-3">Admin Login</h5>

        <?php if ($success != "") { ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>

        <?php if ($error != "") { ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>

        <form method="POST" action="admin_login.php" onsubmit="return validateForm()">
            <div class="mb-3">
                <label class="form-label">Username or Email</label>
                <input type="text" class="form-control" id="login" name="login" placeholder="Enter username or email">
            </div>

            <div class="mb-2">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter password">
            </div>

            <div class="forgot-link">
                <a href="admin_forgot_password.php">Forgot Password?</a>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>

        <p>Don't have an admin account? <a href="admin_register.php">Register</a></p>
    </div>
</div>

<script>
function validateForm() {
    var login = document.getElementById("login").value.trim();
    var password = document.getElementById("password").value;

    if (login == "") {
        alert("Please enter your username or email");
        return false;
    }

    if (password == "") {
        alert("Please enter your password");
        return false;
    }

    return true;
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>