<?php
// ContactUs.php - support page: contact details + a message form
// Messages are saved in the contact_messages table (see contact_messages.sql)

// 1. Start the session BEFORE any HTML is printed (header.php needs it too)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Database connection
include "dbconnection.php";

$error_message = "";
$success_message = "";

// Values shown in the form. If the user is logged in, name and email are filled in for them.
$full_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : "";
$email = isset($_SESSION['email']) ? $_SESSION['email'] : "";
$subject = "";
$message = "";

// 3. Message shown after a successful send (see the redirect below)
if (isset($_GET['success'])) {
    $success_message = "Thank you! Your message has been sent to our support team.";
}

// 4. Form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    // --- Validation ---
    if ($full_name == "" || $email == "" || $subject == "" || $message == "") {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } elseif (mb_strlen($full_name) > 100 || mb_strlen($email) > 100 || mb_strlen($subject) > 100) {
        $error_message = "Name, email and subject must be 100 characters or less.";
    } elseif (mb_strlen($message) > 2000) {
        $error_message = "Message must be 2000 characters or less.";
    } else {

        // Logged-in users are linked to their account, guests are saved without a user_id
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        $stmt = $conn->prepare("INSERT INTO contact_messages (user_id, full_name, email, subject, message)
                                VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $full_name, $email, $subject, $message);

        if ($stmt->execute()) {
            $stmt->close();
            // Redirect so refreshing the page does not send the message twice
            header("Location: ContactUs.php?success=1");
            exit();
        } else {
            $error_message = "Could not send your message. Please try again.";
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Contact Us - Room Booking System</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

<style>
/* =========================================================
   BASIC PAGE (same as index.php)
   ========================================================= */
body {
    font-family: Arial, sans-serif;
    background: #f5f5f5;
    color: #222222;
}

.page-title {
    font-size: 22px;
    font-weight: bold;
    margin: 30px 0 6px;
}

.page-subtitle {
    font-size: 14px;
    color: #777777;
    margin-bottom: 20px;
}

/* =========================================================
   WHITE CARDS (same look as the cards on index.php)
   ========================================================= */
.card-box {
    background: #fff;
    border: 1px solid #e2e2e2;
    border-radius: 12px;
    padding: 20px;
    height: 100%;
}

.card-box h2 {
    font-size: 16px;
    font-weight: bold;
    margin: 0 0 14px;
}

.card-box label {
    font-size: 13px;
    font-weight: bold;
}

/* Contact details (label on top, value below) */
.contact-item {
    padding: 10px 0;
    border-top: 1px solid #eeeeee;
    font-size: 14px;
}

.contact-item:first-of-type {
    border-top: none;
    padding-top: 0;
}

.contact-label {
    font-size: 12px;
    color: #777777;
}

/* Page bottom space */
.page-bottom {
    padding-bottom: 20px;
}
</style>
</head>

<body>

<?php include "header.php"; ?>

<div class="container page-bottom">

    <h1 class="page-title">Contact Us</h1>
    <p class="page-subtitle">Need help with a booking or have a question? Send us a message.</p>

    <?php if ($success_message != "") { ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php } ?>

    <?php if ($error_message != "") { ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php } ?>

    <div class="row g-4">

        <!-- LEFT: message form -->
        <div class="col-lg-8">
            <div class="card-box">
                <h2>Send us a message</h2>

                <form method="POST" action="ContactUs.php" onsubmit="return validateForm()">

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label for="full_name">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name"
                                   value="<?php echo htmlspecialchars($full_name); ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="email">Email</label>
                            <input type="text" class="form-control" id="email" name="email"
                                   value="<?php echo htmlspecialchars($email); ?>">
                        </div>

                        <div class="col-12">
                            <label for="subject">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject"
                                   placeholder="e.g. Problem with my booking"
                                   value="<?php echo htmlspecialchars($subject); ?>">
                        </div>

                        <div class="col-12">
                            <label for="message">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5"
                                      placeholder="Write your message here"><?php echo htmlspecialchars($message); ?></textarea>
                        </div>

                    </div>

                    <button type="submit" class="btn btn-dark mt-3">Send Message</button>
                </form>
            </div>
        </div>

        <!-- RIGHT: contact details (same details as the footer) -->
        <div class="col-lg-4">
            <div class="card-box">
                <h2>Contact Information</h2>

                <div class="contact-item">
                    <div class="contact-label">Email</div>
                    support@roombooking.com
                </div>

                <div class="contact-item">
                    <div class="contact-label">Phone</div>
                    +977-9800000000
                </div>

                <div class="contact-item">
                    <div class="contact-label">Address</div>
                    Kathmandu, Nepal
                </div>
            </div>
        </div>

    </div>

</div>

<script>
// Simple form check before sending (same style as register.php)
function validateForm() {
    var name = document.getElementById("full_name").value.trim();
    var email = document.getElementById("email").value.trim();
    var subject = document.getElementById("subject").value.trim();
    var message = document.getElementById("message").value.trim();

    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (name == "") {
        alert("Please enter your name");
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

    if (subject == "") {
        alert("Please enter a subject");
        return false;
    }

    if (message == "") {
        alert("Please write your message");
        return false;
    }

    return true;
}
</script>

<?php include "Footer.php"; ?>

</body>
</html>
