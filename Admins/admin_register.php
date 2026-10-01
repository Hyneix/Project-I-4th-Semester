<?php
include "dbconnection.php";

$message = "";
$type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $role_id = intval($_POST['role_id']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $message = "All fields are required.";
        $type = "danger";
    } elseif ($password != $confirm_password) {
        $message = "Passwords do not match.";
        $type = "danger";
    } else {
        $check_email = $conn->prepare("SELECT admin_id FROM admins WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        $check_email->store_result();

        if ($check_email->num_rows > 0) {
            $message = "Email already registered.";
            $type = "danger";
        } else {
            $check_username = $conn->prepare("SELECT admin_id FROM admins WHERE username = ?");
            $check_username->bind_param("s", $username);
            $check_username->execute();
            $check_username->store_result();

            if ($check_username->num_rows > 0) {
                $message = "Username already exists.";
                $type = "danger";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("INSERT INTO admins (username, password, full_name, email, role_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("ssssi", $username, $hashed_password, $full_name, $email, $role_id);

                if ($stmt->execute()) {
                    header("Location: admin_login.php");
                    exit();
                } else {
                    $message = "Registration failed. Please try again.";
                    $type = "danger";
                }
                $stmt->close();
            }
            $check_username->close();
        }
        $check_email->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Register - Room Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: Arial, sans-serif;
        }
        .register-box {
            width: 100%;
            max-width: 450px;
            margin: 50px auto;
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
    </style>
</head>
<body>

<div class="register-box">
    <div class="card shadow-sm">
        <h3>Room Booking System</h3>
        <h5 class="text-center mb-3">Admin Register</h5>

        <?php if ($message != "") { ?>
            <div class="alert alert-<?php echo $type; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>

        <form method="POST" action="admin_register.php" onsubmit="return validateForm()">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text"
                       class="form-control"
                       id="username"
                       name="username"
                       placeholder="Enter username">
            </div>

            <div class="mb-3">
                <label for="full_name" class="form-label">Full Name</label>
                <input type="text"
                       class="form-control"
                       id="full_name"
                       name="full_name"
                       placeholder="Enter full name">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="text"
                       class="form-control"
                       id="email"
                       name="email"
                       placeholder="Enter email">
            </div>

            <div class="mb-3">
                <label for="role_id" class="form-label">Role ID</label>
                <input type="number"
                       class="form-control"
                       id="role_id"
                       name="role_id"
                       placeholder="Enter role ID"
                       min="1">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password"
                       class="form-control"
                       id="password"
                       name="password"
                       placeholder="Enter password (min 6 characters)">
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password"
                       class="form-control"
                       id="confirm_password"
                       name="confirm_password"
                       placeholder="Confirm password">
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Register
            </button>
        </form>

        <p>
            Already have an admin account?
            <a href="admin_login.php">Login</a>
        </p>
    </div>
</div>

<script>
function validateForm() {
    var username = document.getElementById("username").value.trim();
    var fullName = document.getElementById("full_name").value.trim();
    var email = document.getElementById("email").value.trim();
    var roleId = document.getElementById("role_id").value.trim();
    var password = document.getElementById("password").value;
    var confirmPassword = document.getElementById("confirm_password").value;

    var usernamePattern = /^[A-Za-z0-9_]+$/;
    var namePattern = /^[A-Za-z ]+$/;
    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    var passwordPattern = /^.{6,}$/;

    if (username == "") {
        alert("Please enter your username");
        return false;
    }

    if (!usernamePattern.test(username)) {
        alert("Username should contain only letters, numbers, and underscores");
        return false;
    }

    if (fullName == "") {
        alert("Please enter your full name");
        return false;
    }

    if (!namePattern.test(fullName)) {
        alert("Full name should contain only letters and spaces");
        return false;
    }

    if (email == "") {
        alert("Please enter your email");
        return false;
    }

    if (!emailPattern.test(email)) {
        alert("Please enter a valid email");
        return false;
    }

    if (roleId == "" || roleId < 1) {
        alert("Please enter a valid role ID");
        return false;
    }

    if (password == "") {
        alert("Please enter your password");
        return false;
    }

    if (!passwordPattern.test(password)) {
        alert("Password must be at least 6 characters");
        return false;
    }

    if (confirmPassword == "") {
        alert("Please confirm your password");
        return false;
    }

    if (password != confirmPassword) {
        alert("Passwords do not match");
        return false;
    }

    return true;
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>