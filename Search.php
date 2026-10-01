<?php
// Search.php
// Searches the EXISTING rooms table and uses the EXISTING bookings
// table only to check whether a room is already booked.
// No new rooms, no sample data - everything is read from the database.

include "dbconnection.php";   // provides the existing $conn connection

/* ============================================================
   1. READ THE SEARCH VALUES SENT BY THE FORM (GET)
   index.php and this page both send these values
   ============================================================ */
$location   = isset($_GET['location'])   ? trim($_GET['location'])   : "";
$checkin    = isset($_GET['checkin'])    ? trim($_GET['checkin'])    : "";
$checkout   = isset($_GET['checkout'])   ? trim($_GET['checkout'])   : "";
$guests     = isset($_GET['guests'])     ? (int) $_GET['guests']     : 0;
$start_time = isset($_GET['start_time']) ? trim($_GET['start_time']) : "";
$end_time   = isset($_GET['end_time'])   ? trim($_GET['end_time'])   : "";

// If the user gave a check-in date but no check-out date,
// we only check that one day.
if ($checkout == "") {
    $checkout = $checkin;
}

/* ============================================================
   2. FUNCTION: IS THIS ROOM ALREADY BOOKED?
   Uses the existing bookings table.
   A booking conflicts when:
       booking_date is between the requested dates
   AND existing start_time < requested end_time
   AND existing end_time   > requested start_time
   Example conflict:  existing 10:00-12:00, requested 11:00-13:00
   Example no conflict: existing 10:00-12:00, requested 12:00-14:00
   ============================================================ */
function isRoomBooked($conn, $room_id, $checkin, $checkout, $start_time, $end_time)
{
    // No date chosen -> do not block the room, only rooms.status is used
    if ($checkin == "") {
        return false;
    }

    // If no time was chosen, the room is needed for the whole day
    if ($start_time == "") {
        $start_time = "00:00:00";
    }
    if ($end_time == "") {
        $end_time = "23:59:59";
    }

    // Only ACTIVE booking_status values block a room.
    // This project uses 'confirmed' (see UserProfile.php),
    // 'pending' is also treated as active. Adjust if needed.
    $sql = "SELECT booking_id
            FROM bookings
            WHERE room_id = ?
              AND booking_status IN ('pending', 'confirmed')
              AND booking_date >= ?
              AND booking_date <= ?
              AND start_time < ?
              AND end_time > ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $room_id, $checkin, $checkout, $end_time, $start_time);
    $stmt->execute();
    $bookings = $stmt->get_result();
    $stmt->close();

    // If at least one overlapping booking exists -> room is booked
    return ($bookings->num_rows > 0);
}

/* ============================================================
   3. SEARCH THE EXISTING rooms TABLE
   Prepared statements are used because the user typed the values.
   ============================================================ */
$sql = "SELECT room_id, room_name, room_type, capacity, location, description, image, status
        FROM rooms
        WHERE 1 = 1";

$types  = "";       // bind types: s = string, i = integer
$params = array();

// Text search: match room name, room type or location
if ($location != "") {
    $like = "%" . $location . "%";
    $sql .= " AND (room_name LIKE ?
                 OR room_type LIKE ?
                 OR location  LIKE ?)";
    $types   .= "sss";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

// Capacity: the room must fit the number of guests
if ($guests > 0) {
    $sql     .= " AND capacity >= ?";
    $types   .= "i";
    $params[] = $guests;
}

$sql .= " ORDER BY room_id";

$stmt = $conn->prepare($sql);
if ($types != "") {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$rooms = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Search Rooms - Room Booking System</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Same Bootstrap version as index.php -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

<style>
/* ---------- Basic page (same as index.php) ---------- */
body {
    font-family: Arial, sans-serif;
    background: #f8f9fa;
    color: #212529;
}

/* ---------- Search form box (same box style as index.php) ---------- */
.search-form-box {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-top: 30px;
}

.search-form-box label {
    font-size: 13px;
    font-weight: bold;
    color: #555;
}

/* ---------- Results title ---------- */
.results-title {
    font-size: 18px;
    font-weight: bold;
    margin: 25px 0 15px;
}

/* ---------- Room result cards (same card look as index/userProfile) ---------- */
.room-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.room-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
}

/* Room image (top of the card) */
.room-image {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 6px;
    margin-bottom: 15px;
}

/* Shown when a room has no image or the file is missing */
.no-image {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e9ecef;
    color: #6c757d;
    font-size: 13px;
}

.room-name {
    font-size: 17px;
    font-weight: bold;
    margin-bottom: 4px;
}

.room-type {
    font-size: 13px;
    color: #0d6efd;
    margin-bottom: 8px;
}

.room-info {
    font-size: 13px;
    color: #555;
    margin-bottom: 4px;
}

.room-desc {
    font-size: 13px;
    color: #777;
    margin: 10px 0;
}

/* Availability badge */
.badge-available,
.badge-unavailable {
    display: inline-block;
    font-size: 12px;
    padding: 4px 10px;
    border-radius: 12px;
    margin-bottom: 10px;
}

.badge-available {
    background: #d1e7dd;
    color: #0f5132;
}

.badge-unavailable {
    background: #f8d7da;
    color: #842029;
}

/* No rooms found message */
.no-results {
    text-align: center;
    color: #888;
    padding: 30px;
    font-size: 14px;
}

/* ---------- Footer (same as index.php) ---------- */
footer {
    margin-top: 40px;
    padding: 20px 0;
    text-align: center;
    color: #6c757d;
    font-size: 14px;
}

/* ---------- Dark theme (same dark-mode colors as header.php) ---------- */
body.dark-mode {
    background: #212529;
    color: #f8f9fa;
}

body.dark-mode .search-form-box,
body.dark-mode .room-card {
    background: #2b3035;
    border-color: #495057;
    color: #e0e0e0;
}

body.dark-mode .search-form-box label {
    color: #ccc;
}

body.dark-mode .room-info,
body.dark-mode .room-desc {
    color: #adb5bd;
}

body.dark-mode .results-title {
    color: #f8f9fa;
}

body.dark-mode .no-image {
    background: #343a40;
    color: #adb5bd;
}

body.dark-mode .no-results {
    color: #adb5bd;
}

body.dark-mode footer {
    color: #adb5bd;
}
</style>
</head>

<body>

<?php include "header.php"; ?>

<div class="container">

    <!-- Search form: sends the values back to this same page -->
    <div class="search-form-box">
        <form action="Search.php" method="GET">

            <div class="row g-3 align-items-end">

                <div class="col-md-3">
                    <label for="location">Room name / type / location</label>
                    <input type="text" class="form-control" id="location" name="location"
                           placeholder="e.g. Kathmandu, Lab, Meeting"
                           value="<?php echo htmlspecialchars($location); ?>">
                </div>

                <div class="col-md-2">
                    <label for="checkin">Check-in</label>
                    <input type="date" class="form-control" id="checkin" name="checkin"
                           value="<?php echo htmlspecialchars($checkin); ?>">
                </div>

                <div class="col-md-2">
                    <label for="checkout">Check-out</label>
                    <input type="date" class="form-control" id="checkout" name="checkout"
                           value="<?php echo htmlspecialchars($checkout); ?>">
                </div>

                <div class="col-md-2">
                    <label for="start_time">Start time</label>
                    <input type="time" class="form-control" id="start_time" name="start_time"
                           value="<?php echo htmlspecialchars($start_time); ?>">
                </div>

                <div class="col-md-1">
                    <label for="end_time">End time</label>
                    <input type="time" class="form-control" id="end_time" name="end_time"
                           value="<?php echo htmlspecialchars($end_time); ?>">
                </div>

                <div class="col-md-1">
                    <label for="guests">Guests</label>
                    <input type="number" class="form-control" id="guests" name="guests"
                           min="1" placeholder="1"
                           value="<?php echo $guests > 0 ? $guests : ""; ?>">
                </div>

                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>

            </div>
        </form>
    </div>

    <!-- Search results -->
    <h2 class="results-title">
        Available Rooms
        <small style="font-weight:normal; font-size:13px; color:#888;">
            (<?php echo $rooms->num_rows; ?> room<?php echo $rooms->num_rows == 1 ? "" : "s"; ?> found)
        </small>
    </h2>

    <?php if ($rooms->num_rows > 0) { ?>

        <div class="room-grid">

            <?php while ($room = $rooms->fetch_assoc()) { ?>

                <?php
                // 1. rooms.status must allow the room
                //    (adjust the value below to match your real status values,
                //     e.g. 'Available' if your table stores it with a capital A)
                $roomOpen = (strtolower($room['status']) == 'available');

                // 2. bookings table must not have an overlapping booking
                $booked = isRoomBooked($conn, $room['room_id'],
                                       $checkin, $checkout,
                                       $start_time, $end_time);

                // Room is only available when BOTH checks pass
                $available = ($roomOpen && !$booked);

                // The room image is shown only if the image column has a filename
                // AND that file really exists inside the images folder
                $hasImage = (!empty($room['image']) && file_exists("images/" . $room['image']));
                ?>

                <div class="room-card">

                    <?php if ($hasImage) { ?>
                        <img src="images/<?php echo htmlspecialchars($room['image']); ?>"
                             alt="<?php echo htmlspecialchars($room['room_name']); ?>"
                             class="room-image">
                    <?php } else { ?>
                        <div class="room-image no-image">No Image Available</div>
                    <?php } ?>

                    <div class="room-name">
                        <?php echo htmlspecialchars($room['room_name']); ?>
                    </div>

                    <div class="room-type">
                        <?php echo htmlspecialchars($room['room_type']); ?>
                    </div>

                    <div class="room-info">
                        Capacity: <?php echo (int) $room['capacity']; ?> person(s)
                    </div>

                    <div class="room-info">
                        Location: <?php echo htmlspecialchars($room['location']); ?>
                    </div>

                    <div class="room-desc">
                        <?php echo htmlspecialchars($room['description']); ?>
                    </div>

                    <?php if ($available) { ?>
                        <span class="badge-available">Available</span>
                        <br>
                        <!-- Point this to your existing booking page -->
                        <a href="booking.php?room_id=<?php echo (int) $room['room_id']; ?>"
                           class="btn btn-primary btn-sm">
                            Book Room
                        </a>
                    <?php } else { ?>
                        <span class="badge-unavailable">Unavailable</span>
                        <br>
                        <button class="btn btn-secondary btn-sm" disabled>
                            Booked
                        </button>
                    <?php } ?>

                </div>

            <?php } ?>

        </div>

    <?php } else { ?>

        <div class="no-results">
            No rooms matched your search. Try a different name, type or location.
        </div>

    <?php } ?>

</div>

<footer>
    &copy; 2026 Room Booking System. All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
<?php
$stmt->close();
$conn->close();
?>