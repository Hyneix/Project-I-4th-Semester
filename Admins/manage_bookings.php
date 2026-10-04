<?php
include "admin_header.php";

// Status filter (manage_bookings.php?status=Pending). Empty means "All".
$allowed_filters = ['Pending', 'Approved', 'Cancelled'];
$filter = '';
if (isset($_GET['status'])) {
    $filter = $_GET['status'];
}
if (isset($_POST['filter'])) {
    $filter = $_POST['filter'];
}
if (!in_array($filter, $allowed_filters)) {
    $filter = '';
}

// Approve or cancel a booking
if (isset($_POST['action'])) {

    $booking_id = (int) $_POST['booking_id'];
    $action = $_POST['action'];
    $msg = "";
    $err = "";

    // Load the booking
    $stmt = mysqli_prepare($conn, "SELECT room_id, check_in_date, check_out_date, booking_status
                                   FROM bookings WHERE booking_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $booking_id);
    mysqli_stmt_execute($stmt);
    $booking = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$booking) {
        $err = "Booking not found.";

    } elseif ($action == 'approve') {

        if ($booking['booking_status'] != 'Pending') {
            $err = "Only pending bookings can be approved.";
        } else {
            // Is there another approved booking for the same room on overlapping dates?
            $clash = 0;
            if ($booking['check_in_date'] != null && $booking['check_out_date'] != null) {
                $stmt = mysqli_prepare($conn, "SELECT COUNT(*) FROM bookings
                                               WHERE room_id = ? AND booking_id != ?
                                                 AND booking_status = 'Approved'
                                                 AND check_in_date < ? AND check_out_date > ?");
                mysqli_stmt_bind_param($stmt, "iiss", $booking['room_id'], $booking_id,
                                       $booking['check_out_date'], $booking['check_in_date']);
                mysqli_stmt_execute($stmt);
                $clash = mysqli_fetch_row(mysqli_stmt_get_result($stmt))[0];
            }

            if ($clash > 0) {
                $err = "This room already has an approved booking for those dates.";
            } else {
                $stmt = mysqli_prepare($conn, "UPDATE bookings SET booking_status = 'Approved'
                                               WHERE booking_id = ? AND booking_status = 'Pending'");
                mysqli_stmt_bind_param($stmt, "i", $booking_id);
                mysqli_stmt_execute($stmt);
                $msg = "Booking #$booking_id approved.";
            }
        }

    } elseif ($action == 'cancel') {

        if ($booking['booking_status'] == 'Cancelled') {
            $err = "This booking is already cancelled.";
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE bookings SET booking_status = 'Cancelled' WHERE booking_id = ?");
            mysqli_stmt_bind_param($stmt, "i", $booking_id);
            mysqli_stmt_execute($stmt);
            $msg = "Booking #$booking_id cancelled.";
        }
    }

    // Go back to the same filter with a message
    $url = "manage_bookings.php?status=" . urlencode($filter);
    if ($err != "") {
        $url .= "&error=" . urlencode($err);
    } else {
        $url .= "&msg=" . urlencode($msg);
    }
    header("Location: " . $url);
    exit();
}

// Get bookings (bookings + users + rooms)
$sql = "SELECT b.booking_id, u.full_name, r.room_name, b.check_in_date, b.check_out_date,
               b.guests, b.total_price, b.booking_status
        FROM bookings b
        JOIN users u ON b.user_id = u.user_id
        JOIN rooms r ON b.room_id = r.room_id";

if ($filter != '') {
    $stmt = mysqli_prepare($conn, $sql . " WHERE b.booking_status = ? ORDER BY b.booking_id DESC");
    mysqli_stmt_bind_param($stmt, "s", $filter);
} else {
    $stmt = mysqli_prepare($conn, $sql . " ORDER BY b.booking_id DESC");
}
mysqli_stmt_execute($stmt);
$bookings = mysqli_stmt_get_result($stmt);
?>

<div class="page-top">
    <h1 class="page-title">Bookings</h1>

    <form method="GET" action="manage_bookings.php" class="d-flex gap-2">
        <select name="status" class="form-select">
            <option value="">All</option>
            <?php foreach ($allowed_filters as $f) { ?>
                <option value="<?php echo $f; ?>" <?php if ($filter == $f) echo 'selected'; ?>>
                    <?php echo $f; ?>
                </option>
            <?php } ?>
        </select>
        <button type="submit" class="btn btn-dark">Filter</button>
    </form>
</div>

<div class="table-box">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Room</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Guests</th>
                <th>Total</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>

        <?php if (mysqli_num_rows($bookings) > 0) { ?>
            <?php while ($b = mysqli_fetch_assoc($bookings)) { ?>
            <tr>
                <td><?php echo (int) $b['booking_id']; ?></td>
                <td><?php echo htmlspecialchars($b['full_name']); ?></td>
                <td><?php echo htmlspecialchars($b['room_name']); ?></td>
                <td><?php echo $b['check_in_date'] ? date('d M Y', strtotime($b['check_in_date'])) : '-'; ?></td>
                <td><?php echo $b['check_out_date'] ? date('d M Y', strtotime($b['check_out_date'])) : '-'; ?></td>
                <td><?php echo $b['guests'] !== null ? (int) $b['guests'] : '-'; ?></td>
                <td><?php echo $b['total_price'] !== null ? 'Rs. ' . number_format($b['total_price'], 2) : '-'; ?></td>

                <td>
                    <span class="status-badge status-<?php echo strtolower($b['booking_status']); ?>">
                        <?php echo htmlspecialchars($b['booking_status']); ?>
                    </span>
                </td>

                <td class="text-nowrap">
                    <?php if ($b['booking_status'] == 'Pending') { ?>
                        <form method="POST" action="manage_bookings.php" class="d-inline">
                            <input type="hidden" name="booking_id" value="<?php echo (int) $b['booking_id']; ?>">
                            <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                            <button type="submit" name="action" value="approve" class="btn btn-dark btn-sm">Approve</button>
                        </form>
                    <?php } ?>

                    <?php if ($b['booking_status'] != 'Cancelled') { ?>
                        <form method="POST" action="manage_bookings.php" class="d-inline"
                              onsubmit="return confirm('Cancel this booking?');">
                            <input type="hidden" name="booking_id" value="<?php echo (int) $b['booking_id']; ?>">
                            <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                            <button type="submit" name="action" value="cancel" class="btn btn-outline-dark btn-sm">Cancel</button>
                        </form>
                    <?php } else { ?>
                        -
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
        <?php } else { ?>
            <tr><td colspan="9" class="text-center text-muted">No bookings found.</td></tr>
        <?php } ?>

        </tbody>
    </table>
</div>

</div><!-- end .page-content -->

<?php $base = "../"; include "../Footer.php"; ?>
</body>
</html>