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

<!-- Bootstrap CSS is only here because header.php uses its dark-mode switch -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

<style>
body {
    font-family: Arial, sans-serif;
    background-color: #ffffff;
    color: #222222;
}

/* Two columns: form on the left, room on the right */
.booking-page {
    display: flex;
    flex-wrap: wrap;
}

.booking-form {
    flex: 1;
    min-width: 300px;
    padding: 25px;
}

.room-details {
    flex: 1;
    min-width: 300px;
    padding: 25px;
    background-color: #f5f5f5;
    border-left: 1px solid #dddddd;
}

.page-title {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 5px;
}

.back-link {
    display: inline-block;
    margin-bottom: 15px;
    color: #222222;
    text-decoration: none;
}

/* Each step is a section separated by a line */
.step {
    border-top: 1px solid #dddddd;
    padding: 20px 0;
    max-width: 480px;
}

.step h2 {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 12px;
}

.booking-form label {
    display: block;
    font-size: 13px;
    margin-bottom: 4px;
}

.booking-form input {
    width: 100%;
    padding: 10px;
    border: 1px solid #cccccc;
    border-radius: 6px;
    margin-bottom: 12px;
}

.booking-form input:focus {
    border-color: #555555;
    outline: none;
}

.booking-form input[readonly] {
    background-color: #eeeeee;
}

/* Room card on the right */
.room-card {
    background-color: #ffffff;
    border: 1px solid #dddddd;
    border-radius: 10px;
    padding: 15px;
    max-width: 480px;
}

.room-image {
    width: 100%;
    height: 220px;
    object-fit: cover;
    border-radius: 8px;
}

.no-image {
    background-color: #e9ecef;
    color: #6c757d;
    text-align: center;
    line-height: 220px;
}

.room-name {
    font-size: 18px;
    font-weight: bold;
    margin-top: 12px;
}

.room-info {
    font-size: 14px;
    color: #555555;
    margin-bottom: 3px;
}

.room-description {
    font-size: 14px;
    color: #666666;
    margin-top: 10px;
}

/* Price summary */
.summary {
    border-top: 1px solid #dddddd;
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
    border-top: 1px solid #dddddd;
    padding-top: 10px;
    margin-top: 10px;
    font-weight: bold;
}

/* Green Book Now button */
.book-button {
    display: block;
    width: 100%;
    max-width: 480px;
    margin-top: 20px;
    padding: 12px;
    background-color: #2e7d32;
    color: #ffffff;
    border: none;
    border-radius: 25px;
    font-size: 16px;
    font-weight: bold;
    text-align: center;
    text-decoration: none;
    cursor: pointer;
}

.book-button:hover {
    background-color: #1b5e20;
}

/* Messages */
.message {
    max-width: 480px;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 6px;
    font-size: 14px;
}

.message-error {
    background-color: #333333;
    color: #ffffff;
}

.message-success {
    background-color: #e8f5e9;
    border: 1px solid #2e7d32;
}

.not-found {
    text-align: center;
    padding: 60px 15px;
}

/* Dark mode (header.php adds "dark-mode") */
body.dark-mode {
    background-color: #212529;
}

body.dark-mode .room-details {
    background-color: #1b1e21;
    border-left-color: #343a40;
}

body.dark-mode .room-card {
    background-color: #2b3035;
    border-color: #495057;
}

body.dark-mode .room-info,
body.dark-mode .room-description {
    color: #adb5bd;
}

body.dark-mode .back-link {
    color: #e0e0e0;
}

body.dark-mode .step,
body.dark-mode .summary,
body.dark-mode .summary-total {
    border-color: #495057;
}

body.dark-mode .booking-form input {
    background-color: #343a40;
    border-color: #495057;
    color: #ffffff;
}

body.dark-mode .message-success {
    background-color: #1b3a1e;
    color: #ffffff;
}

/* Small screens: columns stack, no side line */
@media (max-width: 700px) {
    .room-details {
        border-left: none;
    }
}
</style>
</head>

<body>

<?php include "header.php"; ?>

<?php if (!$room) { ?>

    <!-- Room not found -->
    <div class="not-found">
        <h1 class="page-title">Room not found</h1>
        <p>Sorry, this room does not exist.</p>
        <a href="Search.php" class="btn btn-dark">Back to Search</a>
    </div>

<?php } else { ?>

    <!-- One form around both columns, so the Book Now button can sit on the right -->
    <form method="POST" action="Booking.php?room_id=<?php echo (int) $room['room_id']; ?>"
          id="bookingForm" onsubmit="return validateBooking();">

        <div class="booking-page">

            <!-- ============ LEFT: booking form ============ -->
            <div class="booking-form">

                <a href="Search.php" class="back-link">&larr; Back to search</a>

                <h1 class="page-title">Book <?php echo htmlspecialchars($room['room_name']); ?></h1>

                <?php if ($success_message != "") { ?>
                    <div class="message message-success"><?php echo htmlspecialchars($success_message); ?></div>
                <?php } ?>

                <?php if ($error_message != "") { ?>
                    <div class="message message-error"><?php echo htmlspecialchars($error_message); ?></div>
                <?php } ?>

                <?php if (!$is_logged_in) { ?>

                    <div class="step">
                        <h2>Please login to book a room.</h2>
                        <a href="login.php" class="btn btn-dark">Login</a>
                    </div>

                <?php } else { ?>

                    <!-- Step 1: dates and guests -->
                    <div class="step">
                        <h2>Step 1: Your stay</h2>

                        <label for="check_in">Check-in date</label>
                        <input type="date" id="check_in" name="check_in"
                               min="<?php echo date('Y-m-d'); ?>"
                               value="<?php echo htmlspecialchars($check_in); ?>"
                               onchange="calculateTotal()">

                        <label for="check_out">Check-out date</label>
                        <input type="date" id="check_out" name="check_out"
                               value="<?php echo htmlspecialchars($check_out); ?>"
                               onchange="calculateTotal()">

                        <label for="guests">Guests (maximum <?php echo (int) $room['capacity']; ?>)</label>
                        <input type="number" id="guests" name="guests"
                               min="1" max="<?php echo (int) $room['capacity']; ?>"
                               value="<?php echo htmlspecialchars($guests); ?>">
                    </div>

                    <!-- Step 2: user information -->
                    <div class="step">
                        <h2>Step 2: Your details</h2>

                        <label for="full_name">Name</label>
                        <input type="text" id="full_name" readonly
                               value="<?php echo htmlspecialchars($_SESSION['full_name']); ?>">

                        <label for="purpose">Purpose / note (optional)</label>
                        <input type="text" id="purpose" name="purpose" maxlength="255"
                               placeholder="e.g. Family trip"
                               value="<?php echo htmlspecialchars($purpose); ?>">
                    </div>

                    <!-- Step 3: fixed times used by the system -->
                    <div class="step">
                        <h2>Step 3: House rules</h2>
                        <div class="room-info">Check-in: from 2 PM</div>
                        <div class="room-info">Check-out: until 11 AM</div>
                    </div>

                <?php } ?>

            </div>


            <!-- ============ RIGHT: room details (from the database) ============ -->
            <div class="room-details">

                <div class="room-card">

                    <?php if (!empty($room['image'])) { ?>
                        <img src="images/<?php echo htmlspecialchars($room['image']); ?>"
                             alt="<?php echo htmlspecialchars($room['room_name']); ?>"
                             class="room-image">
                    <?php } else { ?>
                        <div class="room-image no-image">No Image Available</div>
                    <?php } ?>

                    <div class="room-name"><?php echo htmlspecialchars($room['room_name']); ?></div>
                    <div class="room-info">Type: <?php echo htmlspecialchars($room['room_type']); ?></div>
                    <div class="room-info">Location: <?php echo htmlspecialchars($room['location']); ?></div>
                    <div class="room-info">Capacity: <?php echo (int) $room['capacity']; ?> guest(s)</div>
                    <div class="room-info">Status: <?php echo htmlspecialchars($room['status']); ?></div>

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

                <?php if ($is_logged_in) { ?>
                    <button type="submit" class="book-button">Book Now</button>
                <?php } else { ?>
                    <a href="login.php" class="book-button">Login to Book</a>
                <?php } ?>

            </div>

        </div>

    </form>

<?php } ?>

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
