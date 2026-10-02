<?php
// Booking.php - show one room and let a logged-in user book it
// Link to this page:  Booking.php?room_id=ROOM_ID

// 1. Start the session BEFORE any HTML is printed (header.php needs it too)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Database connection
include "dbconnection.php";

$room = null;               // will hold the room row from the database
$error_message = "";        // shown on the page if something is wrong
$success_message = "";

// Values typed in the form (kept on the page if there is an error)
$check_in = "";
$check_out = "";
$guests = "";
$purpose = "";




// 3. Get room_id from the URL and make sure it is a valid number
if (isset($_GET['room_id'])) {
    $room_id = intval($_GET['room_id']);
} else {
    $room_id = 0;
}

$_SESSION['after_login'] = "Booking.php?room_id=" . $room_id;


// 4. Get the room from the rooms table (only the ID comes from the URL)
if ($room_id > 0) {
    $sql = "SELECT room_id, room_name, room_type, capacity, price_per_night,
                   location, description, image, status
            FROM rooms
            WHERE room_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $room = $result->fetch_assoc();
    $stmt->close();
}

// Is the user logged in? (same session variable as header.php)
$is_logged_in = isset($_SESSION['user_id']);

// Message shown after a successful booking (see redirect below)
if (isset($_GET['success'])) {
    $success_message = "Booking submitted! Your booking is Pending until it is approved.";
}

// 5. Form submitted (Book Now button)
if ($room && $is_logged_in && $_SERVER["REQUEST_METHOD"] == "POST") {

    $check_in = trim($_POST['check_in']);
    $check_out = trim($_POST['check_out']);
    $guests = (int) $_POST['guests'];
    $purpose = trim($_POST['purpose']);

    $today = date("Y-m-d");
    $price = (float) $room['price_per_night'];

    // --- Validation ---
    if ($check_in == "" || $check_out == "" || $guests < 1) {
        $error_message = "Please fill in check-in, check-out and guests.";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $check_in) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $check_out)) {
        $error_message = "Please enter valid dates.";
    } elseif ($check_in < $today) {
        $error_message = "Check-in date cannot be in the past.";
    } elseif ($check_out <= $check_in) {
        $error_message = "Check-out must be after check-in.";
    } elseif ($guests > $room['capacity']) {
        $error_message = "This room allows a maximum of " . (int) $room['capacity'] . " guests.";
    } elseif (strtolower($room['status']) != "available") {
        $error_message = "Sorry, this room is not available.";
    } elseif ($price <= 0) {
        $error_message = "The price of this room is not set yet.";
    }

    // --- Check that the room is free on these dates ---
    if ($error_message == "") {

        // A booking overlaps if it starts before our check-out
        // AND ends after our check-in. Cancelled bookings are ignored.
        // Old bookings (no check-in/out dates) count as one day.
        $sql = "SELECT COUNT(*) FROM bookings
                WHERE room_id = ?
                AND booking_status <> 'Cancelled'
                AND COALESCE(check_in_date, booking_date) < ?
                AND COALESCE(check_out_date, DATE_ADD(booking_date, INTERVAL 1 DAY)) > ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $room_id, $check_out, $check_in);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_row();
        $stmt->close();

        if ($row[0] > 0) {
            $error_message = "Sorry, this room is already booked for the selected dates.";
        }
    }

    // --- Save the booking ---
    if ($error_message == "") {

        // Number of nights and total price are calculated again here
        // (never trust the JavaScript total)
        $nights = round((strtotime($check_out) - strtotime($check_in)) / 86400);
        $total_price = $price * $nights;

        // The old columns are required, so we fill them like this:
        // booking_date = check-in day, start_time = check-in time,
        // end_time = check-out time
        $start_time = "14:00:00";
        $end_time = "11:00:00";

        // If the user wrote nothing, the room name is saved as the purpose
        if ($purpose == "") {
            $purpose = $room['room_name'];
        }

        $user_id = $_SESSION['user_id'];

        $sql = "INSERT INTO bookings
                (user_id, room_id, booking_date, start_time, end_time, purpose,
                 booking_status, check_in_date, check_out_date, guests, total_price)
                VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissssssid", $user_id, $room_id, $check_in, $start_time,
                          $end_time, $purpose, $check_in, $check_out, $guests, $total_price);

        if ($stmt->execute()) {
            $stmt->close();
            // Redirect so refreshing the page does not book twice
            header("Location: Booking.php?room_id=" . $room_id . "&success=1");
            exit();
        } else {
            $error_message = "Booking failed. Please try again.";
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book Room - Room Booking System</title>
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
    margin: 10px 0 18px;
}

.back-link {
    display: inline-block;
    margin-top: 30px;
    color: #222222;
    text-decoration: none;
    font-size: 14px;
}

/* =========================================================
   WHITE CARDS (same look as the cards on index.php)
   ========================================================= */
.card-box {
    background: #fff;
    border: 1px solid #e2e2e2;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 18px;
}

.card-box h2 {
    font-size: 16px;
    font-weight: bold;
    margin: 0 0 14px;
}

.card-box label {
    font-size: 12px;
    font-weight: bold;
    color: #555;
    margin-bottom: 2px;
}

.info-line {
    font-size: 13px;
    color: #777;
    margin-bottom: 3px;
}

/* =========================================================
   ROOM CARD (right side)
   ========================================================= */
.room-card {
    background: #fff;
    border: 1px solid #e2e2e2;
    border-radius: 12px;
    overflow: hidden;
}

.room-img-wrap {
    position: relative;
}

.room-img-wrap img,
.no-image {
    width: 100%;
    height: 220px;
    object-fit: cover;
    display: block;
}

.no-image {
    background: #e9ecef;
    color: #6c757d;
    text-align: center;
    line-height: 220px;
}

/* small status badge, top left (same as index.php) */
.room-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    font-size: 11px;
    font-weight: bold;
    padding: 4px 10px;
    border-radius: 12px;
}

.badge-available   { background: #d6f5d6; color: #1c6e1c; }
.badge-unavailable { background: #eeeeee; color: #666666; }

.room-body {
    padding: 12px 14px 14px;
}

.room-name {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 4px;
}

.room-description {
    font-size: 13px;
    color: #777;
    margin-top: 10px;
}

/* Price summary */
.summary {
    border-top: 1px solid #e2e2e2;
    margin-top: 15px;
    padding-top: 12px;
    font-size: 14px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 6px;
}

.summary-total {
    border-top: 1px solid #e2e2e2;
    padding-top: 10px;
    margin-top: 10px;
    font-weight: bold;
}

/* Book Now button (blue, like the other buttons) */
.book-button {
    display: block;
    width: 100%;
    margin-top: 18px;
    padding: 12px;
    background: #0d6efd;
    color: #fff;
    border: none;
    border-radius: 25px;
    font-size: 16px;
    font-weight: bold;
    text-align: center;
    text-decoration: none;
    cursor: pointer;
}

.book-button:hover {
    background: #0b5ed7;
    color: #fff;
}

.page-bottom {
    padding-bottom: 40px;
}

/* =========================================================
   DARK MODE (header.php adds "dark-mode" to body)
   ========================================================= */
body.dark-mode .card-box,
body.dark-mode .room-card {
    background: #2b3035;
    border-color: #495057;
    color: #e0e0e0;
}

body.dark-mode .page-title {
    color: #f1f1f1;
}

body.dark-mode .back-link {
    color: #e0e0e0;
}

body.dark-mode .card-box label {
    color: #ccc;
}

body.dark-mode .info-line,
body.dark-mode .room-description {
    color: #adb5bd;
}

body.dark-mode .summary,
body.dark-mode .summary-total {
    border-color: #495057;
}

body.dark-mode .form-control {
    background-color: #343a40;
    border-color: #495057;
    color: #fff;
}

body.dark-mode .form-control[readonly] {
    background-color: #2b3035;
}
</style>
</head>

<body>

<?php include "header.php"; ?>

<div class="container page-bottom">

<?php if (!$room) { ?>

    <!-- Room not found -->
    <div class="card-box text-center" style="margin-top: 40px;">
        <h1 class="page-title">Room not found</h1>
        <p>Sorry, this room does not exist.</p>
        <a href="Search.php" class="btn btn-dark">Back to Search</a>
    </div>

<?php } else { ?>

    <a href="Search.php" class="back-link">&larr; Back to search</a>

    <h1 class="page-title">Book <?php echo htmlspecialchars($room['room_name']); ?></h1>

    <?php if ($success_message != "") { ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php } ?>

    <?php if ($error_message != "") { ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php } ?>

    <!-- One form around both columns, so the Book Now button can sit on the right -->
    <form method="POST" action="Booking.php?room_id=<?php echo (int) $room['room_id']; ?>"
          id="bookingForm" onsubmit="return validateBooking();">

        <div class="row g-4">

            <!-- ============ LEFT: booking form ============ -->
            <div class="col-lg-7">

                <?php if (!$is_logged_in) { ?>

                    <div class="card-box">
                        <h2>Please login to book a room.</h2>
                        <a href="login.php" class="btn btn-dark">Login</a>
                    </div>

                <?php } else { ?>

                    <!-- Step 1: dates and guests -->
                    <div class="card-box">
                        <h2>Step 1: Your stay</h2>

                        <div class="mb-3">
                            <label for="check_in">Check-in date</label>
                            <input type="date" id="check_in" name="check_in" class="form-control"
                                   min="<?php echo date('Y-m-d'); ?>"
                                   value="<?php echo htmlspecialchars($check_in); ?>"
                                   onchange="calculateTotal()">
                        </div>

                        <div class="mb-3">
                            <label for="check_out">Check-out date</label>
                            <input type="date" id="check_out" name="check_out" class="form-control"
                                   value="<?php echo htmlspecialchars($check_out); ?>"
                                   onchange="calculateTotal()">
                        </div>

                        <div>
                            <label for="guests">Guests (maximum <?php echo (int) $room['capacity']; ?>)</label>
                            <input type="number" id="guests" name="guests" class="form-control"
                                   min="1" max="<?php echo (int) $room['capacity']; ?>"
                                   value="<?php echo htmlspecialchars($guests); ?>">
                        </div>
                    </div>

                    <!-- Step 2: user information -->
                    <div class="card-box">
                        <h2>Step 2: Your details</h2>

                        <div class="mb-3">
                            <label for="full_name">Name</label>
                            <input type="text" id="full_name" class="form-control" readonly
                                   value="<?php echo htmlspecialchars($_SESSION['full_name']); ?>">
                        </div>

                        <div>
                            <label for="purpose">Purpose / note (optional)</label>
                            <input type="text" id="purpose" name="purpose" class="form-control"
                                   maxlength="255" placeholder="e.g. Family trip"
                                   value="<?php echo htmlspecialchars($purpose); ?>">
                        </div>
                    </div>

                    <!-- Step 3: fixed times used by the system -->
                    <div class="card-box">
                        <h2>Step 3: House rules</h2>
                        <div class="info-line">Check-in: from 2 PM</div>
                        <div class="info-line">Check-out: until 11 AM</div>
                    </div>

                <?php } ?>

            </div>


            <!-- ============ RIGHT: room details (from the database) ============ -->
            <div class="col-lg-5">

                <div class="room-card">

                    <div class="room-img-wrap">
                        <?php if (!empty($room['image'])) { ?>
                            <img src="images/<?php echo htmlspecialchars($room['image']); ?>"
                                 alt="<?php echo htmlspecialchars($room['room_name']); ?>">
                        <?php } else { ?>
                            <div class="no-image">No Image Available</div>
                        <?php } ?>

                        <?php if (strtolower($room['status']) == "available") { ?>
                            <span class="room-badge badge-available">Available</span>
                        <?php } else { ?>
                            <span class="room-badge badge-unavailable"><?php echo htmlspecialchars($room['status']); ?></span>
                        <?php } ?>
                    </div>

                    <div class="room-body">

                        <div class="room-name"><?php echo htmlspecialchars($room['room_name']); ?></div>
                        <div class="info-line">Type: <?php echo htmlspecialchars($room['room_type']); ?></div>
                        <div class="info-line">Location: <?php echo htmlspecialchars($room['location']); ?></div>
                        <div class="info-line">Capacity: <?php echo (int) $room['capacity']; ?> guest(s)</div>

                        <?php if (!empty($room['description'])) { ?>
                            <div class="room-description">
                                <?php echo nl2br(htmlspecialchars($room['description'])); ?>
                            </div>
                        <?php } ?>

                        <!-- Price summary (JavaScript fills in the numbers) -->
                        <div class="summary" id="priceBox"
                             data-price="<?php echo (float) $room['price_per_night']; ?>">

                            <div class="summary-row">
                                <span>Check-in</span>
                                <span id="showCheckIn">-</span>
                            </div>
                            <div class="summary-row">
                                <span>Check-out</span>
                                <span id="showCheckOut">-</span>
                            </div>
                            <div class="summary-row">
                                <span>Price per night</span>
                                <span>Rs. <?php echo number_format((float) $room['price_per_night']); ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Number of nights</span>
                                <span id="showNights">0</span>
                            </div>
                            <div class="summary-row summary-total">
                                <span>TOTAL</span>
                                <span id="showTotal">Rs. 0</span>
                            </div>
                        </div>

                    </div>

                </div>

                <?php if ($is_logged_in) { ?>
                    <button type="submit" class="book-button">Book Now</button>
                <?php } else { ?>
                    <a href="login.php" class="book-button">Login to Book</a>
                <?php } ?>

            </div>

        </div>

    </form>

<?php } ?>

</div>

<?php include "Footer.php"; ?>

<script>
/* Calculate number of nights and total price (runs when a date changes) */
function calculateTotal() {
    var checkIn = document.getElementById("check_in");
    var checkOut = document.getElementById("check_out");
    var priceBox = document.getElementById("priceBox");

    if (!checkIn || !checkOut) {
        return;
    }

    var price = parseFloat(priceBox.getAttribute("data-price"));

    // Check-out cannot be on or before the check-in day
    if (checkIn.value !== "") {
        var next = new Date(checkIn.value);
        next.setDate(next.getDate() + 1);
        checkOut.min = next.toISOString().split("T")[0];
    }

    document.getElementById("showCheckIn").innerText = checkIn.value || "-";
    document.getElementById("showCheckOut").innerText = checkOut.value || "-";

    var nights = 0;

    if (checkIn.value !== "" && checkOut.value !== "") {
        var start = new Date(checkIn.value);
        var end = new Date(checkOut.value);
        nights = Math.round((end - start) / (1000 * 60 * 60 * 24));
    }

    if (nights < 0) {
        nights = 0;
    }

    document.getElementById("showNights").innerText = nights;
    document.getElementById("showTotal").innerText = "Rs. " + (price * nights).toLocaleString();
}

/* Basic checks before the form is sent (onsubmit) */
function validateBooking() {
    var checkIn = document.getElementById("check_in").value;
    var checkOut = document.getElementById("check_out").value;
    var guests = document.getElementById("guests");

    if (checkIn === "" || checkOut === "" || guests.value === "") {
        alert("Please fill in check-in, check-out and guests.");
        return false;
    }

    if (checkOut <= checkIn) {
        alert("Check-out must be after check-in.");
        return false;
    }

    if (parseInt(guests.value) < 1 || parseInt(guests.value) > parseInt(guests.max)) {
        alert("Guests must be between 1 and " + guests.max + ".");
        return false;
    }

    return true;
}

/* Show the numbers when the page opens (also after an error) */
calculateTotal();
</script>

</body>
</html>
<?php
$conn->close();
?>