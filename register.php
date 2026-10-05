<?php
include "dbconnection.php";

$message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($full_name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $message = "All fields are required.";
    } elseif ($password != $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        $check_email = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        $check_email->store_result();

        if ($check_email->num_rows > 0) {
            $message = "Email already registered.";
        } else {
            $check_name = $conn->prepare("SELECT user_id FROM users WHERE full_name = ?");
            $check_name->bind_param("s", $full_name);
            $check_name->execute();
            $check_name->store_result();

            if ($check_name->num_rows > 0) {
                $message = "Username already exists.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $full_name, $email, $phone, $hashed_password);

                if ($stmt->execute()) {
                    header("Location: login.php");
                    exit();
                } else {
                    $message = "Registration failed. Please try again.";
                }
                $stmt->close();
            }
            $check_name->close();
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
    <title>Register - Room Booking System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <link href="auth.css" rel="stylesheet">
</head>
<body>

<a href="index.php" class="logo">
    <img src="images/logo.png" alt="Room Booking System">
    <span>Room Booking System</span>
</a>

<div class="login-container">
    <div class="login-box">

        <div class="card-icon">
            <i class="bi bi-person-plus"></i>
        </div>

        <h1>Create your account</h1>
        <p class="subtitle">Register to book rooms quickly and easily.</p>

        <?php if ($message != "") { ?>
            <div class="alert alert-danger">
                <?php echo $message; ?>
            </div>
        <?php } ?>

        <form method="POST" action="register.php" onsubmit="return validateForm()">

            <div class="form-group">
                <label for="full_name" class="visually-hidden">Full Name</label>
                <div class="input-box">
                    <i class="bi bi-person field-icon"></i>
                    <input type="text" id="full_name" name="full_name" placeholder="Enter full name">
                </div>
            </div>

            <div class="form-group">
                <label for="email" class="visually-hidden">Email</label>
                <div class="input-box">
                    <i class="bi bi-envelope field-icon"></i>
                    <input type="text" id="email" name="email" placeholder="Enter email">
                </div>
            </div>

            <div class="form-group">
                <label for="phone" class="visually-hidden">Phone</label>
                <div class="input-box">
                    <i class="bi bi-telephone field-icon"></i>
                    <input type="text" id="phone" name="phone" placeholder="Enter 10 digit phone number">
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="visually-hidden">Password</label>
                <div class="input-box">
                    <i class="bi bi-lock-fill field-icon"></i>
                    <input type="password" id="password" name="password" placeholder="Enter password (min 6 characters)">
                    <button type="button" class="toggle-password" onclick="togglePassword('password', this)" aria-label="Show or hide password">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password" class="visually-hidden">Confirm Password</label>
                <div class="input-box">
                    <i class="bi bi-lock-fill field-icon"></i>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password">
                    <button type="button" class="toggle-password" onclick="togglePassword('confirm_password', this)" aria-label="Show or hide password">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="login-button">Register</button>
        </form>

        <p class="switch-text">
            Already have an account?
            <a href="login.php">Login</a>
        </p>
    </div>
</div>

<script>
function validateForm() {
    var name = document.getElementById("full_name").value.trim();
    var email = document.getElementById("email").value.trim();
    var phone = document.getElementById("phone").value.trim();
    var password = document.getElementById("password").value;
    var confirmPassword = document.getElementById("confirm_password").value;

    var namePattern = /^[A-Za-z ]+$/;
    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    var phonePattern = /^[0-9]{10}$/;
    var passwordPattern = /^.{6,}$/;

    if (name == "") {
        alert("Please enter your name");
        return false;
    }

    if (!namePattern.test(name)) {
        alert("Name should contain only letters and spaces");
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

    if (phone == "") {
        alert("Please enter your phone number");
        return false;
    }

    if (!phonePattern.test(phone)) {
        alert("Phone number must contain exactly 10 digits");
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