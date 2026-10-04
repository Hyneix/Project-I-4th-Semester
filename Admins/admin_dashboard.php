<?php
include "admin_header.php";

// Use Nepal time so "today" is correct
date_default_timezone_set('Asia/Kathmandu');
$today = date('Y-m-d');

// Summary numbers
$result = mysqli_query($conn, "SELECT COUNT(*) FROM rooms");
$total_rooms = mysqli_fetch_row($result)[0];

$result = mysqli_query($conn, "SELECT COUNT(*) FROM bookings WHERE booking_status = 'Pending'");
$pending_bookings = mysqli_fetch_row($result)[0];

$result = mysqli_query($conn, "SELECT COUNT(*) FROM users");
$total_users = mysqli_fetch_row($result)[0];

// Approved bookings that start today
$result = mysqli_query($conn, "SELECT COUNT(*) FROM bookings
                               WHERE check_in_date = '$today' AND booking_status = 'Approved'");
$todays_checkins = mysqli_fetch_row($result)[0];

// Messages: total and the 5 newest
$result = mysqli_query($conn, "SELECT COUNT(*) FROM contact_messages");
$total_messages = mysqli_fetch_row($result)[0];

$recent_messages = mysqli_query($conn, "SELECT full_name, subject, message, created_at
                                        FROM contact_messages
                                        ORDER BY message_id DESC
                                        LIMIT 5");
?>

<div class="page-top">
    <h1 class="page-title">Dashboard</h1>
</div>

<div class="row g-3">

    <div class="col-md-6">
        <div class="summary-card">
            <div class="label">Total Rooms</div>
            <div class="number"><?php echo $total_rooms; ?></div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="summary-card">
            <div class="label">Pending Bookings</div>
            <div class="number"><?php echo $pending_bookings; ?></div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="summary-card">
            <div class="label">Total Users</div>
            <div class="number"><?php echo $total_users; ?></div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="summary-card">
            <div class="label">Today's Check-ins</div>
            <div class="number"><?php echo $todays_checkins; ?></div>
        </div>
    </div>

    <!-- Recent messages: each row opens the Messages page -->
    <div class="col-12">
        <div class="box">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h2 class="box-title">Recent Messages <span class="count-text">(<?php echo $total_messages; ?> total)</span></h2>
                <a href="manage_messages.php" class="btn btn-outline-dark btn-sm">View All</a>
            </div>

            <?php if (mysqli_num_rows($recent_messages) > 0) { ?>
                <?php while ($message = mysqli_fetch_assoc($recent_messages)) { ?>
                    <a href="manage_messages.php" class="message-row">
                        <div class="d-flex justify-content-between gap-3">
                            <span class="message-title">
                                <?php echo htmlspecialchars($message['full_name']); ?> - <?php echo htmlspecialchars($message['subject']); ?>
                            </span>
                            <span class="message-date"><?php echo date('d M Y', strtotime($message['created_at'])); ?></span>
                        </div>
                        <div class="message-preview"><?php echo htmlspecialchars(mb_strimwidth($message['message'], 0, 100, '...')); ?></div>
                    </a>
                <?php } ?>
            <?php } else { ?>
                <p class="text-muted mb-0">No messages yet.</p>
            <?php } ?>
        </div>
    </div>

</div>

</div><!-- end .page-content -->

<?php $base = "../"; include "../Footer.php"; ?>
</body>
</html>