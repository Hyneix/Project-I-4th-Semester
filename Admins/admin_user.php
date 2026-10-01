<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include "dbconnection.php";

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "User not found.";
    exit();
}

/* Get user's bookings */
$stmt2 = $conn->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY booking_date DESC");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$bookings = $stmt2->get_result();
$stmt2->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View User - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #2b2b2b;
            font-family: Arial, sans-serif;
            color: #e0e0e0;
        }
        .navbar {
            background-color: #1f1f1f;
            padding: 15px;
            border-bottom: 1px solid #444;
        }
        .navbar-brand {
            color: #fff;
            font-size: 20px;
            font-weight: bold;
            text-decoration: none;
        }
        .card {
            background-color: #363636;
            border: none;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .card-title {
            font-size: 15px;
            color: #ccc;
            margin-bottom: 15px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            margin-bottom: 8px;
            padding: 8px;
            background-color: #404040;
            border-radius: 4px;
        }
        .info-label { color: #888; }
        .info-value { color: #ccc; }
        .btn-back {
            background-color: #4a5568;
            color: #fff;
            border: none;
            padding: 6px 15px;
            font-size: 13px;
            border-radius: 4px;
            text-decoration: none;
        }
        .btn-back:hover { background-color: #556677; color: #fff; }
        .table-dark-custom {
            width: 100%;
            font-size: 13px;
        }
        .table-dark-custom th {
            color: #888;
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #444;
        }
        .table-dark-custom td {
            color: #ccc;
            padding: 8px;
            border-bottom: 1px solid #444;
        }
        .container-fluid { padding: 20px; }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <a class="navbar-brand" href="admin.php">Admin Panel</a>
        <a href="admin.php" class="btn-back">Back to Dashboard</a>
    </div>
</nav>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">User Details</h6>

                    <div class="info-row">
                        <span class="info-label">Full Name</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['full_name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">User ID</span>
                        <span class="info-value"><?php echo $user['user_id']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Registered</span>
                        <span class="info-value"><?php echo $user['created_at']; ?></span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">User Bookings</h6>

                    <?php if ($bookings->num_rows > 0) { ?>
                        <table class="table-dark-custom">
                            <tr>
                                <th>Booking ID</th>
                                <th>Room</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                            <?php while ($b = $bookings->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $b['booking_id']; ?></td>
                                    <td><?php echo htmlspecialchars($b['purpose'] ?? 'Room #' . $b['room_id']); ?></td>
                                    <td><?php echo $b['booking_date']; ?></td>
                                    <td><?php echo $b['booking_status']; ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    <?php } else { ?>
                        <p style="color:#666; text-align:center;">No bookings found.</p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>