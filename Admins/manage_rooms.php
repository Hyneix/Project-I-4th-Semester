<?php
include "admin_header.php";

$show = 6;
if (isset($_GET['show'])) {
    $show = (int) $_GET['show'];
}
if (isset($_POST['show'])) {
    $show = (int) $_POST['show'];
}
if ($show < 6) {
    $show = 6;
}

// Delete a room
if (isset($_POST['delete_room'])) {
    $room_id = (int) $_POST['room_id'];

    $stmt = mysqli_prepare($conn, "DELETE FROM rooms WHERE room_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $room_id);
    mysqli_stmt_execute($stmt);

    header("Location: manage_rooms.php?show=" . $show . "&msg=" . urlencode("Room deleted."));
    exit();
}

$result = mysqli_query($conn, "SELECT COUNT(*) FROM rooms");
$total_rooms = mysqli_fetch_row($result)[0];

$stmt = mysqli_prepare($conn, "SELECT * FROM rooms ORDER BY room_id LIMIT ?");
mysqli_stmt_bind_param($stmt, "i", $show);
mysqli_stmt_execute($stmt);
$rooms = mysqli_stmt_get_result($stmt);
?>

<div class="page-top">
    <h1 class="page-title">Rooms <span class="text-muted" style="font-size:15px; font-weight:normal;">
        (showing <?php echo min($show, $total_rooms); ?> of <?php echo $total_rooms; ?>)</span></h1>
    <a href="room_form.php" class="btn btn-dark">+ Add Room</a>
</div>

<?php if (mysqli_num_rows($rooms) > 0) { ?>

    <div class="row g-4">

        <?php while ($room = mysqli_fetch_assoc($rooms)) { ?>
        <div class="col-md-6 col-lg-4">
            <div class="admin-room-card">

                <!-- Image + status label -->
                <div class="admin-room-img">
                    <?php if (!empty($room['image'])) { ?>
                        <img src="../images/<?php echo htmlspecialchars($room['image']); ?>"
                             alt="<?php echo htmlspecialchars($room['room_name']); ?>">
                    <?php } else { ?>
                        <div class="admin-room-noimg">No image</div>
                    <?php } ?>

                    <span class="status-badge status-<?php echo strtolower($room['status']); ?> admin-room-status">
                        <?php echo htmlspecialchars($room['status']); ?>
                    </span>
                </div>

                <div class="admin-room-body">

                    <div class="admin-room-name">
                        <?php echo htmlspecialchars($room['room_name']); ?>
                        <span class="admin-room-id">#<?php echo (int) $room['room_id']; ?></span>
                    </div>

                    <p class="admin-room-desc">
                        <?php
                        if (!empty($room['description'])) {
                            echo htmlspecialchars(mb_strimwidth($room['description'], 0, 90, '...'));
                        } else {
                            echo 'No description.';
                        }
                        ?>
                    </p>

                    <div class="admin-room-row"><span>Type</span><span><?php echo htmlspecialchars((string) $room['room_type']); ?></span></div>
                    <div class="admin-room-row"><span>Location</span><span><?php echo htmlspecialchars((string) $room['location']); ?></span></div>
                    <div class="admin-room-row"><span>Capacity</span><span><?php echo (int) $room['capacity']; ?> guests</span></div>
                    <div class="admin-room-row"><span>Price / Night</span><span>Rs. <?php echo number_format($room['price_per_night'], 2); ?></span></div>
                    <div class="admin-room-row"><span>Added On</span><span><?php echo date('d M Y', strtotime($room['created_at'])); ?></span></div>

                    <div class="admin-room-actions">
                        <a href="room_form.php?room_id=<?php echo (int) $room['room_id']; ?>"
                           class="btn btn-outline-dark btn-sm">Edit</a>

                        <form method="POST" action="manage_rooms.php" class="d-inline"
                              onsubmit="return confirm('Delete this room? Its bookings will also be deleted.');">
                            <input type="hidden" name="room_id" value="<?php echo (int) $room['room_id']; ?>">
                            <input type="hidden" name="show" value="<?php echo $show; ?>">
                            <button type="submit" name="delete_room" class="btn btn-dark btn-sm">Delete</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
        <?php } ?>

    </div>

    <?php if ($total_rooms > $show) { ?>
        <div id="more" class="text-center mt-4">
            <a href="manage_rooms.php?show=<?php echo $show + 6; ?>#more" class="btn btn-dark px-4">See More</a>
        </div>
    <?php } ?>

<?php } else { ?>
    <div class="box text-center text-muted">No rooms found.</div>
<?php } ?>

</div>

<?php $base = "../"; include "../Footer.php"; ?>
</body>
</html>