<?php

include "admin_header.php";

$filter = "";

if (isset($_GET['status'])) {
    $filter = $_GET['status'];
}

$allowed = ["Pending", "Approved", "Cancelled"];

if (!in_array($filter, $allowed)) {
    $filter = "";
}

if (isset($_POST['action'])) {

    $booking_id = (int)$_POST['booking_id'];
    $action = $_POST['action'];

    $sql = "SELECT *
            FROM bookings
            WHERE booking_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();

    if (!$booking) {

        $error = "Booking not found.";

    } elseif ($action == "approve") {

        if ($booking['booking_status'] != "Pending") {

            $error = "Only pending bookings can be approved.";

        } else {

            $sql = "SELECT booking_id
                    FROM bookings
                    WHERE room_id = ?
                    AND booking_id != ?
                    AND booking_status = 'Approved'
                    AND check_in_date < ?
                    AND check_out_date > ?";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "iiss",
                $booking['room_id'],
                $booking_id,
                $booking['check_out_date'],
                $booking['check_in_date']
            );

            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows > 0) {

                $error = "Another approved booking already exists for these dates.";

            } else {

                $sql = "UPDATE bookings
                        SET booking_status = 'Approved'
                        WHERE booking_id = ?";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $booking_id);
                $stmt->execute();

                $message = "Booking approved successfully.";
            }
        }

    } elseif ($action == "cancel") {

        $sql = "UPDATE bookings
                SET booking_status = 'Cancelled'
                WHERE booking_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();

        $message = "Booking cancelled successfully.";
    }

    $url = "manage_bookings.php";

    if ($filter != "") {
        $url .= "?status=" . urlencode($filter);
    }

    header("Location: " . $url);
    exit();
}

$sql = "SELECT
            b.booking_id,
            u.full_name,
            r.room_name,
            b.check_in_date,
            b.check_out_date,
            b.guests,
            b.total_price,
            b.booking_status
        FROM bookings b
        JOIN users u ON b.user_id = u.user_id
        JOIN rooms r ON b.room_id = r.room_id";

if ($filter != "") {
    $sql .= " WHERE b.booking_status = ?";
}

$sql .= " ORDER BY b.booking_id DESC";

$stmt = $conn->prepare($sql);

if ($filter != "") {
    $stmt->bind_param("s", $filter);
}

$stmt->execute();

$bookings = $stmt->get_result();

?>

<div class="page-top">

    <h1 class="page-title">Bookings</h1>

    <form method="GET" class="d-flex gap-2">

        <select name="status" class="form-select">

            <option value="">All</option>

            <?php foreach ($allowed as $status) { ?>

                <option value="<?php echo $status; ?>" <?php if ($filter == $status) echo "selected"; ?>>
                    <?php echo $status; ?>
                </option>

            <?php } ?>

        </select>

        <button class="btn btn-dark" type="submit">
            Filter
        </button>

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
                <th>Action</th>
            </tr>

        </thead>

        <tbody>

            <?php if ($bookings->num_rows > 0) { ?>

                <?php while ($booking = $bookings->fetch_assoc()) { ?>

                    <tr>

                        <td>
                            <?php echo $booking['booking_id']; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($booking['full_name']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($booking['room_name']); ?>
                        </td>

                        <td>
                            <?php echo date("d M Y", strtotime($booking['check_in_date'])); ?>
                        </td>

                        <td>
                            <?php echo date("d M Y", strtotime($booking['check_out_date'])); ?>
                        </td>

                        <td>
                            <?php echo $booking['guests']; ?>
                        </td>

                        <td>
                            Rs. <?php echo number_format($booking['total_price'], 2); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($booking['booking_status']); ?>
                        </td>

                        <td>

                            <?php if ($booking['booking_status'] == "Pending") { ?>

                                <form method="POST" style="display:inline;">

                                    <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">

                                    <button type="submit" name="action" value="approve" class="btn btn-dark btn-sm">
                                        Approve
                                    </button>

                                </form>

                            <?php } ?>

                            <?php if ($booking['booking_status'] != "Cancelled") { ?>

                                <form method="POST" style="display:inline;" onsubmit="return confirm('Cancel this booking?');">

                                    <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">

                                    <button type="submit" name="action" value="cancel" class="btn btn-outline-dark btn-sm">
                                        Cancel
                                    </button>

                                </form>

                            <?php } ?>

                        </td>

                    </tr>

                <?php } ?>

            <?php } else { ?>

                <tr>

                    <td colspan="9" class="text-center">
                        No bookings found.
                    </td>

                </tr>

            <?php } ?>

        </tbody>

    </table>

</div>

</div>

<?php

$base = "../";

include "../Footer.php";

?>

</body>
</html>