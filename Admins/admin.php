<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include "dbconnection.php";

$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'];

/* Get admin details */
$stmt = $conn->prepare("SELECT created_at FROM admins WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin_result = $stmt->get_result();
$admin_data = $admin_result->fetch_assoc();
$stmt->close();

$admin_created = $admin_data ? date("m/d/Y", strtotime($admin_data['created_at'])) : "N/A";

/* Get initials for avatar */
$name_parts = explode(" ", $admin_name);
$initials = strtoupper(substr($name_parts[0], 0, 1));
if (isset($name_parts[1])) {
    $initials .= strtoupper(substr($name_parts[1], 0, 1));
}

/* Summary counts */
$total_users = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$total_bookings = $conn->query("SELECT COUNT(*) AS c FROM bookings")->fetch_assoc()['c'];
$pending_bookings = $conn->query("SELECT COUNT(*) AS c FROM bookings WHERE booking_status = 'pending'")->fetch_assoc()['c'];
$total_rooms = $conn->query("SELECT COUNT(*) AS c FROM rooms")->fetch_assoc()['c'];

/* All users */
$users_result = $conn->query("SELECT user_id, full_name, email, phone FROM users ORDER BY user_id DESC LIMIT 10");

/* All bookings */
$bookings_result = $conn->query("SELECT b.*, u.full_name AS user_name FROM bookings b JOIN users u ON b.user_id = u.user_id ORDER BY b.booking_date DESC LIMIT 10");

/* Pending bookings for approval */
$pending_result = $conn->query("SELECT b.*, u.full_name AS user_name FROM bookings b JOIN users u ON b.user_id = u.user_id WHERE b.booking_status = 'pending' ORDER BY b.booking_date ASC LIMIT 10");

/* All rooms */
$rooms_result = $conn->query("SELECT * FROM rooms ORDER BY room_id DESC LIMIT 10");

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Room Booking System</title>
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

        .nav-link {
            color: #b0b0b0;
            font-size: 14px;
            text-decoration: none;
            margin-left: 25px;
        }

        .nav-link:hover {
            color: #fff;
        }

        .card {
            background-color: #363636;
            border: none;
            margin-bottom: 20px;
            color: #e0e0e0;
            border-radius: 8px;
        }

        .card-title {
            font-size: 15px;
            margin-bottom: 15px;
            color: #ccc;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #555;
            margin: 0 auto;
            text-align: center;
            line-height: 100px;
            font-size: 32px;
            color: #fff;
        }

        .profile-name {
            text-align: center;
            font-size: 17px;
            margin-top: 10px;
            color: #fff;
        }

        .profile-sub {
            text-align: center;
            font-size: 12px;
            color: #888;
        }

        .section-heading {
            font-size: 12px;
            color: #888;
            margin-bottom: 8px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .info-label {
            color: #888;
        }

        .info-value {
            color: #ccc;
        }

        .tag {
            display: inline-block;
            background-color: #4a5568;
            padding: 4px 10px;
            margin: 2px;
            font-size: 11px;
            border-radius: 3px;
            color: #ddd;
        }

        .notification-box {
            background-color: #2d3748;
            padding: 12px;
            border-radius: 6px;
            margin-top: 15px;
        }

        .notification-box p {
            font-size: 11px;
            color: #a0aec0;
            margin: 0;
            line-height: 1.5;
        }

        .stat-box {
            background-color: #404040;
            padding: 15px;
            border-radius: 4px;
            text-align: center;
        }

        .stat-number {
            font-size: 24px;
            color: #fff;
            font-weight: bold;
        }

        .stat-label {
            font-size: 11px;
            color: #888;
            margin-top: 5px;
        }

        .table-dark-custom {
            width: 100%;
            font-size: 12px;
        }

        .table-dark-custom th {
            color: #888;
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #444;
            font-weight: normal;
        }

        .table-dark-custom td {
            color: #ccc;
            padding: 8px;
            border-bottom: 1px solid #444;
        }

        .mini-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #555;
            text-align: center;
            line-height: 32px;
            font-size: 12px;
            color: #fff;
            margin-right: 10px;
            display: inline-block;
        }

        .btn-action {
            background-color: #4a5568;
            color: #fff;
            border: none;
            padding: 4px 10px;
            font-size: 11px;
            border-radius: 3px;
            text-decoration: none;
        }

        .btn-action:hover {
            background-color: #556677;
            color: #fff;
        }

        .btn-approve {
            background-color: #38a169;
            color: #fff;
            border: none;
            padding: 4px 10px;
            font-size: 11px;
            border-radius: 3px;
        }

        .btn-approve:hover {
            background-color: #2f855a;
        }

        .btn-cancel {
            background-color: #e53e3e;
            color: #fff;
            border: none;
            padding: 4px 10px;
            font-size: 11px;
            border-radius: 3px;
        }

        .btn-cancel:hover {
            background-color: #c53030;
        }

        .btn-add {
            background-color: #4a5568;
            color: #fff;
            border: none;
            padding: 8px;
            width: 100%;
            font-size: 12px;
            border-radius: 4px;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .btn-add:hover {
            background-color: #556677;
            color: #fff;
        }

        .btn-logout {
            background-color: #e53e3e;
            color: #fff;
            border: none;
            padding: 5px 15px;
            font-size: 13px;
            border-radius: 4px;
            text-decoration: none;
        }

        .btn-logout:hover {
            background-color: #c53030;
        }

        .empty-msg {
            text-align: center;
            color: #666;
            font-size: 13px;
            padding: 15px;
        }

        .room-item {
            background-color: #404040;
            padding: 10px;
            margin-bottom: 6px;
            border-radius: 4px;
            font-size: 12px;
        }

        .container-fluid {
            padding: 20px;
        }

        .row {
            margin: 0 -10px;
        }

        .col-md-3,
        .col-md-6 {
            padding: 0 10px;
        }

        .card-body {
            padding: 15px;
        }
    </style>
</head>

<body>

<!-- Navigation -->
<nav class="navbar">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <a class="navbar-brand" href="admin.php">Room Booking System</a>
        <div class="d-flex align-items-center">
            <a class="nav-link" href="#">Home</a>
            <a class="nav-link" href="#">Search rooms</a>
            <a class="nav-link" href="admin.php">Admin Dashboard</a>
            <a href="admin_logout.php" class="btn-logout ms-3">Logout</a>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">

        <!-- Left Sidebar - Admin Profile -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">

                    <div class="profile-avatar"><?php echo $initials; ?></div>

                    <div class="profile-name"><?php echo htmlspecialchars($admin_name); ?></div>
                    <div class="profile-sub">Administrator</div>

                    <div class="text-start" style="margin-top:20px;">
                        <p class="section-heading">Account info</p>
                        <div class="info-row">
                            <span class="info-label">Admin ID</span>
                            <span class="info-value"><?php echo $admin_id; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Account Created</span>
                            <span class="info-value"><?php echo $admin_created; ?></span>
                        </div>
                    </div>

                    <div class="text-start" style="margin-top:15px;">
                        <p class="section-heading">Role</p>
                        <span class="tag">Administrator</span>
                        <span class="tag">Room Booking System</span>
                    </div>

                    <div class="notification-box text-start">
                        <p><strong style="color:#fff;">Admin Panel</strong></p>
                        <p style="margin-top:5px;">Manage users, bookings, and rooms from this dashboard.</p>
                    </div>

                </div>
            </div>
        </div>

        <!-- Center Content -->
        <div class="col-md-6">

            <!-- Summary Stats -->
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Dashboard Summary</h6>
                    <div class="row">
                        <div class="col-3">
                            <div class="stat-box">
                                <div class="stat-number"><?php echo $total_users; ?></div>
                                <div class="stat-label">Total Users</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-box">
                                <div class="stat-number"><?php echo $total_bookings; ?></div>
                                <div class="stat-label">Total Bookings</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-box">
                                <div class="stat-number"><?php echo $pending_bookings; ?></div>
                                <div class="stat-label">Pending</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-box">
                                <div class="stat-number"><?php echo $total_rooms; ?></div>
                                <div class="stat-label">Total Rooms</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manage Users -->
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Manage Users</h6>

                    <?php if ($users_result->num_rows > 0) { ?>
                        <table class="table-dark-custom">
                            <tr>
                                <th>Profile</th>
                                <th>Username</th>
                                <th>ID</th>
                                <th>Email</th>
                                <th>Action</th>
                            </tr>
                            <?php while ($user = $users_result->fetch_assoc()) {
                                $u_initial = strtoupper(substr($user['full_name'], 0, 1));
                            ?>
                                <tr>
                                    <td><span class="mini-avatar"><?php echo $u_initial; ?></span></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo $user['user_id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <a href="admin_user.php?id=<?php echo $user['user_id']; ?>" class="btn-action">View</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    <?php } else { ?>
                        <div class="empty-msg">No users found</div>
                    <?php } ?>
                </div>
            </div>

            <!-- Total Bookings -->
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Total Bookings</h6>

                    <?php if ($bookings_result->num_rows > 0) { ?>
                        <table class="table-dark-custom">
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Room</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                            </tr>
                            <?php while ($b = $bookings_result->fetch_assoc()) {
                                $display = !empty($b['purpose']) ? $b['purpose'] : "Room #" . $b['room_id'];
                                $time = date("g:i A", strtotime($b['start_time'])) . " - " . date("g:i A", strtotime($b['end_time']));
                            ?>
                                <tr>
                                    <td><?php echo $b['booking_id']; ?></td>
                                    <td><?php echo htmlspecialchars($b['user_name']); ?></td>
                                    <td><?php echo htmlspecialchars($display); ?></td>
                                    <td><?php echo $b['booking_date']; ?></td>
                                    <td><?php echo $time; ?></td>
                                    <td><?php echo $b['booking_status']; ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    <?php } else { ?>
                        <div class="empty-msg">No bookings found</div>
                    <?php } ?>
                </div>
            </div>

            <!-- Approve / Cancel Bookings -->
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Approve / Cancel Bookings</h6>

                    <?php if ($pending_result->num_rows > 0) { ?>
                        <table class="table-dark-custom">
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Room</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            <?php while ($p = $pending_result->fetch_assoc()) {
                                $display = !empty($p['purpose']) ? $p['purpose'] : "Room #" . $p['room_id'];
                            ?>
                                <tr>
                                    <td><?php echo $p['booking_id']; ?></td>
                                    <td><?php echo htmlspecialchars($p['user_name']); ?></td>
                                    <td><?php echo htmlspecialchars($display); ?></td>
                                    <td><?php echo $p['booking_date']; ?></td>
                                    <td><?php echo $p['booking_status']; ?></td>
                                    <td>
                                        <form method="POST" action="admin_booking_action.php" style="display:inline;">
                                            <input type="hidden" name="booking_id" value="<?php echo $p['booking_id']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn-approve">Approve</button>
                                        </form>
                                        <form method="POST" action="admin_booking_action.php" style="display:inline; margin-left:5px;">
                                            <input type="hidden" name="booking_id" value="<?php echo $p['booking_id']; ?>">
                                            <input type="hidden" name="action" value="cancel">
                                            <button type="submit" class="btn-cancel" onclick="return confirm('Are you sure you want to cancel this booking?');">Cancel</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    <?php } else { ?>
                        <div class="empty-msg">No pending bookings</div>
                    <?php } ?>
                </div>
            </div>

        </div>

        <!-- Right Sidebar -->
        <div class="col-md-3">

            <!-- Manage Rooms -->
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Manage Rooms</h6>

                    <?php if ($rooms_result->num_rows > 0) { ?>
                        <?php while ($room = $rooms_result->fetch_assoc()) { ?>
                            <div class="room-item">
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <span><strong><?php echo htmlspecialchars($room['room_name']); ?></strong></span>
                                    <span style="font-size:11px; color:#888;">#<?php echo $room['room_id']; ?></span>
                                </div>
                                <div style="font-size:11px; color:#888; margin-top:3px;">
                                    <?php echo htmlspecialchars($room['location']); ?> &bull; <?php echo htmlspecialchars($room['room_type']); ?> &bull; $<?php echo $room['price']; ?>
                                </div>
                                <div style="font-size:11px; color:#888; margin-top:3px;">
                                    Status: <?php echo $room['availability']; ?>
                                </div>
                                <div style="margin-top:8px;">
                                    <a href="admin_room_edit.php?id=<?php echo $room['room_id']; ?>" class="btn-action">Manage</a>
                                    <a href="admin_room_delete.php?id=<?php echo $room['room_id']; ?>" class="btn-cancel" style="margin-left:5px;" onclick="return confirm('Are you sure you want to remove this room?');">Remove</a>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="empty-msg">No rooms found</div>
                    <?php } ?>
                </div>
            </div>

            <!-- Add Room Button -->
            <div class="card">
                <div class="card-body">
                    <a href="admin_room_add.php" class="btn-add">+ Add Room</a>
                </div>
            </div>

        </div>

    </div>
</div>

</body>
</html>