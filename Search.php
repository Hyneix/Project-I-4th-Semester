<?php
// Search.php
// Searches the EXISTING rooms table.
// No new rooms, no sample data - everything is read from the database.

// Start the session BEFORE any HTML is printed.
// header.php needs the session, and PHP cannot start it after output has begun.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include "dbconnection.php";   // provides the existing $conn connection

/* ============================================================
   1. READ THE SEARCH VALUES SENT BY THE FORM (GET)
   index.php and this page both send these values
   ============================================================ */
$location = isset($_GET['location']) ? trim($_GET['location']) : "";
$guests   = isset($_GET['guests'])   ? (int) $_GET['guests']   : 0;

/* ============================================================
   2. SEARCH THE EXISTING rooms TABLE
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
    grid-template-columns: repeat(4, 1fr);   /* 4 cards in each row */
    gap: 20px;
}

/* The whole card is a link to Room_Information_Booking.php */
.room-link,
.room-link:hover {
    display: block;
    color: inherit;
    text-decoration: none;
}

.room-card {
    height: 100%;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    cursor: pointer;
    transition: 0.2s;
}

/* Hover: the card lifts a little and gets a blue border */
.room-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    border-color: #0d6efd;
}

/* Room image (top of the card) */
.room-image {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 6px;
    margin-bottom: 12px;
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

.room-name,
.room-type,
.room-info {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
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

/* Description always uses the same space (3 lines), long text is cut with "..." */
.room-desc {
    font-size: 13px;
    line-height: 20px;
    height: 60px;
    color: #777;
    margin: 10px 0;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
}

/* Availability badge */
.badge-available,
.badge-unavailable {
    display: inline-block;
    font-size: 12px;
    padding: 4px 10px;
    border-radius: 12px;
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

                <div class="col-md-7">
                    <label for="location">Room name / type / location</label>
                    <input type="text" class="form-control" id="location" name="location"
                           placeholder="e.g. Kathmandu, Lab, Meeting"
                           value="<?php echo htmlspecialchars($location); ?>">
                </div>

                <div class="col-md-3">
                    <label for="guests">Guests</label>
                    <input type="number" class="form-control" id="guests" name="guests"
                           min="1" placeholder="1"
                           value="<?php echo $guests > 0 ? $guests : ""; ?>">
                </div>

                <div class="col-md-2">
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

            <?php $room_number = 0; ?>

            <?php while ($room = $rooms->fetch_assoc()) { ?>

                <?php
                $room_number++;

                // Only the first 8 rooms are visible at the start.
                // The other rooms are hidden until "Show More" is clicked.
                $hidden_style = "";
                if ($room_number > 8) {
                    $hidden_style = 'style="display:none;"';
                }

                // The room is available when rooms.status is "Available"
                $available = (strtolower($room['status']) == 'available');

                // The room image is shown only if the image column has a filename
                // AND that file really exists inside the images folder
                $hasImage = (!empty($room['image']) && file_exists("images/" . $room['image']));
                ?>

                <!-- The whole card is one link to the room information page -->
                <a href="Room_Information_Booking.php?room_id=<?php echo (int) $room['room_id']; ?>"
                   class="room-link" <?php echo $hidden_style; ?>>

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
                        <?php } else { ?>
                            <span class="badge-unavailable">Unavailable</span>
                        <?php } ?>

                    </div>

                </a>

            <?php } ?>

        </div>

        <?php if ($rooms->num_rows > 8) { ?>
            <div class="text-center mt-4">
                <button type="button" class="btn btn-primary" id="showMoreBtn" onclick="showMoreRooms();">
                    Show More
                </button>
            </div>
        <?php } ?>

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

<script>
/* "Show More" - shows 8 more rooms each time the button is clicked */

// The first 8 rooms are already visible (PHP hides the rest)
let visibleRooms = 8;

function showMoreRooms() {
    // Every room card is inside a link with the class "room-link"
    let rooms = document.querySelectorAll(".room-link");

    // Show the next 8 rooms
    for (let i = visibleRooms; i < visibleRooms + 8 && i < rooms.length; i++) {
        rooms[i].style.display = "block";
    }

    visibleRooms += 8;

    // All rooms are visible now -> hide the button
    if (visibleRooms >= rooms.length) {
        document.getElementById("showMoreBtn").style.display = "none";
    }
}
</script>

</body>
</html>
<?php
$stmt->close();
$conn->close();
?>