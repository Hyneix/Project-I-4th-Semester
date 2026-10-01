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
        $stmt = $conn->prepare("SELECT user_id, full_name, email, password FROM users WHERE full_name = ? OR email = ?");
        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();

            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['full_name'] = $row['full_name'];
                $_SESSION['email'] = $row['email'];

                header("Location: index.php");
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
    <title>Login - Room Booking System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Design for this page (shared by login.php and register.php) -->
    <link href="auth.css" rel="stylesheet">
</head>
<body>

<!-- Logo: replace images/logo.png to change it -->
<a href="index.php" class="logo">
    <img src="images/logo.png" alt="Room Booking System">
    <span>Room Booking System</span>
</a>

<div class="login-container">
    <div class="login-box">

        <div class="card-icon">
            <i class="bi bi-box-arrow-in-right"></i>
        </div>

        <h1>Sign in to your account</h1>
        <p class="subtitle">Find and book your perfect room in seconds.</p>

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

        <form method="POST" action="login.php" onsubmit="return validateForm()">

            <div class="form-group">
                <label for="login" class="visually-hidden">Username or Email</label>
                <div class="input-box">
                    <i class="bi bi-person field-icon"></i>
                    <input type="text" id="login" name="login" placeholder="Enter full name or email">
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="visually-hidden">Password</label>
                <div class="input-box">
                    <i class="bi bi-lock-fill field-icon"></i>
                    <input type="password" id="password" name="password" placeholder="Enter password">
                    <button type="button" class="toggle-password" onclick="togglePassword('password', this)" aria-label="Show or hide password">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
            </div>

            <div class="forgot-link">
                <a href="forgot_password.php">Forgot Password?</a>
            </div>

            <button type="submit" class="login-button">Login</button>
        </form>

        <p class="switch-text">Don't have an account? <a href="register.php">Register</a></p>
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

/* Show / hide password: changes the input type and the eye icon */
function togglePassword(inputId, button) {
    var input = document.getElementById(inputId);
    var icon = button.querySelector("i");

    if (input.type == "password") {
        input.type = "text";
        icon.className = "bi bi-eye";
    } else {
        input.type = "password";
        icon.className = "bi bi-eye-slash";
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>