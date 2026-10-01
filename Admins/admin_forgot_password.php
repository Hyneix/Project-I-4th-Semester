<?php
include "dbconnection.php";

$message = "";
$type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($new_password) || empty($confirm_password)) {
        $message = "All fields are required.";
        $type = "danger";
    } elseif ($new_password != $confirm_password) {
        $message = "New passwords do not match.";
        $type = "danger";
    } else {
        $stmt = $conn->prepare("SELECT admin_id FROM admin WHERE username = ? AND email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $update = $conn->prepare("UPDATE admin SET password = ? WHERE admin_id = ?");
            $update->bind_param("si", $hashed_password, $row['admin_id']);

            if ($update->execute()) {
                header("Location: admin_login.php?reset=success");
                exit();
            } else {
                $message = "Failed to update password. Please try again.";
                $type = "danger";
            }
            $update->close();
        } else {
            $message = "Username and email do not match.";
            $type = "danger";
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
    <title>Admin Forgot Password - Room Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: Arial, sans-serif;
        }
        .forgot-box {
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

<div class="forgot-box">
    <div class="card shadow-sm">
        <h3>Room Booking System</h3>
        <h5 class="text-center mb-3">Admin Reset Password</h5>

        <?php if ($message != "") { ?>
            <div class="alert alert-<?php echo $type; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>

        <form method="POST" action="admin_forgot_password.php" onsubmit="return validateForm()">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username">
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="text" class="form-control" id="email" name="email" placeholder="Enter your email">
            </div>

            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Enter new password (min 6 characters)">
            </div>

            <div class="mb-3">
                <label class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </div>
        </form>

        <p>Remember your password? <a href="admin_login.php">Login</a></p>
    </div>
</div>

<script>
function validateForm() {
    var username = document.getElementById("username").value.trim();
    var email = document.getElementById("email").value.trim();
    var newPassword = document.getElementById("new_password").value;
    var confirmPassword = document.getElementById("confirm_password").value;

    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    var passwordPattern = /^.{6,}$/;

    if (username == "") {
        alert("Please enter your username");
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

    if (newPassword == "") {
        alert("Please enter your new password");
        return false;
    }

    if (!passwordPattern.test(newPassword)) {
        alert("New password must be at least 6 characters");
        return false;
    }

    if (confirmPassword == "") {
        alert("Please confirm your new password");
        return false;
    }

    if (newPassword != confirmPassword) {
        alert("Passwords do not match");
        return false;
    }

    return true;
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>