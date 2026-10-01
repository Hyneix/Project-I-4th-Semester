<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include "dbconnection.php";

$msg = "";
$type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_id = intval($_POST['booking_id']);
    $action = $_POST['action'];

    if ($action == "approve") {
        $stmt = $conn->prepare("UPDATE bookings SET booking_status = 'confirmed' WHERE booking_id = ?");
        $stmt->bind_param("i", $booking_id);
        if ($stmt->execute()) {
            $msg = "Booking approved.";
            $type = "success";
        } else {
            $msg = "Failed to approve booking.";
            $type = "danger";
        }
        $stmt->close();
    } elseif ($action == "cancel") {
        $stmt = $conn->prepare("UPDATE bookings SET booking_status = 'cancelled' WHERE booking_id = ?");
        $stmt->bind_param("i", $booking_id);
        if ($stmt->execute()) {
            $msg = "Booking cancelled.";
            $type = "success";
        } else {
            $msg = "Failed to cancel booking.";
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
    <title>Booking Action</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #2b2b2b; color: #e0e0e0; font-family: Arial; padding: 40px; }
        .card { background-color: #363636; border: none; max-width: 500px; margin: 50px auto; padding: 30px; border-radius: 8px; }
        a { color: #68d391; }
    </style>
</head>
<body>
    <div class="card text-center">
        <?php if ($msg != "") { ?>
            <div class="alert alert-<?php echo $type; ?>"><?php echo $msg; ?></div>
        <?php } ?>
        <a href="admin.php">Back to Dashboard</a>
    </div>
</body>
</html>