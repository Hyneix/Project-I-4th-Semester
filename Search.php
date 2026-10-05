<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include "dbconnection.php";

if (isset($_GET['location'])) {
    $location = trim($_GET['location']);
} else {
    $location = "";
}

if (isset($_GET['guests'])) {
    $guests = (int) $_GET['guests'];
} else {
    $guests = 0;
}

if (isset($_GET['limit'])) {
    $limit = (int) $_GET['limit'];
} else {
    $limit = 8;
}

if ($limit < 8) {
    $limit = 8;
}
if ($limit > 100) {
    $limit = 100;
}

$like = "%" . $location . "%";

$sql = "SELECT COUNT(*) FROM rooms
        WHERE (? = '' OR room_name LIKE ? OR room_type LIKE ? OR location LIKE ?)
        AND capacity >= ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssi", $location, $like, $like, $like, $guests);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_row();
$total_rooms = (int) $row[0];
$stmt->close();


$sql = "SELECT room_id, room_name, room_type, capacity, price_per_night, location,
               LEFT(description, 150) AS description, image, status
        FROM rooms
        WHERE (? = '' OR room_name LIKE ? OR room_type LIKE ? OR location LIKE ?)
        AND capacity >= ?
        ORDER BY room_id
        LIMIT ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssii", $location, $like, $like, $like, $guests, $limit);
$stmt->execute();
$rooms = $stmt->get_result();

$next_limit = $limit + 8;
$show_more_link = "Search.php?location=" . urlencode($location)
                . "&guests=" . $guests
                . "&limit=" . $next_limit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Search Rooms - Room Booking System</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f5f5f5;
    color: #222222;
}

.search-form-box {
    background-color: white;
    border: 1px solid #dddddd;
    border-radius: 8px;
    padding: 20px;
    margin-top: 30px;
}

.search-form-box label {
    font-size: 13px;
    font-weight: bold;
}

.results-title {
    font-size: 18px;
    font-weight: bold;
    margin-top: 25px;
    margin-bottom: 15px;
}

.total-text {
    font-size: 13px;
    font-weight: normal;
    color: #888888;
}

.room-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

@media (max-width: 1000px) {
    .room-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    .room-grid {
        grid-template-columns: 1fr;
    }
}

.room-link {
    display: block;
    color: black;
    text-decoration: none;
}

.room-card {
    background-color: white;
    border: 1px solid #dddddd;
    border-radius: 8px;
    padding: 15px;
    height: 100%;
}

.room-card:hover {
    border-color: #555555;
}

.room-image {
    width: 100%;
    height: 180px;
    object-fit: cover;
    margin-bottom: 10px;
}

.no-image {
    background-color: #e9ecef;
    color: #6c757d;
    font-size: 13px;
    text-align: center;
    line-height: 180px;
}

.room-name {
    font-size: 17px;
    font-weight: bold;
}

.room-type {
    font-size: 13px;
    color: #555555;
    margin-bottom: 8px;
}

.room-info {
    font-size: 13px;
    color: #555555;
}

.room-desc {
    font-size: 13px;
    color: #777777;
    height: 60px;
    overflow: hidden;
    margin-top: 10px;
    margin-bottom: 10px;
}

.room-price {
    font-size: 14px;
    font-weight: bold;
    margin-top: 6px;
}

.book-label {
    float: right;
    background-color: #2e7d32;
    color: #ffffff;
    font-size: 12px;
    padding: 4px 12px;
    border-radius: 12px;
}

.available {
    background-color: #eeeeee;
    color: #222222;
    font-size: 12px;
    padding: 4px 10px;
}

.unavailable {
    background-color: #333333;
    color: #ffffff;
    font-size: 12px;
    padding: 4px 10px;
}

.no-results {
    text-align: center;
    color: #888888;
    padding: 30px;
}
</style>
</head>

<body>

<?php include "header.php"; ?>

<div class="container">

    <div class="search-form-box">
        <form action="Search.php" method="GET">
            <div class="row g-3 align-items-end">

                <div class="col-12 col-md-7">
                    <label for="location">Room name / type / location</label>
                    <input type="text" class="form-control" id="location" name="location"
                           placeholder="e.g. Kathmandu, Deluxe, Family"
                           value="<?php echo htmlspecialchars($location); ?>">
                </div>

                <div class="col-12 col-md-3">
                    <label for="guests">Guests</label>
                    <input type="number" class="form-control" id="guests" name="guests"
                           min="1" placeholder="1"
                           value="<?php if ($guests > 0) { echo $guests; } ?>">
                </div>

                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-dark w-100">Search</button>
                </div>

            </div>
        </form>
    </div>

    <h2 class="results-title">
        Available Rooms
        <span class="total-text">
            (<?php echo $total_rooms; ?> room<?php if ($total_rooms != 1) { echo "s"; } ?> found)
        </span>
    </h2>

    <?php if ($total_rooms > 0) { ?>

        <div class="room-grid">

            <?php while ($room = $rooms->fetch_assoc()) { ?>

                <a href="Booking.php?room_id=<?php echo (int) $room['room_id']; ?>" class="room-link">
                    <div class="room-card">

                        <?php if (!empty($room['image'])) { ?>
                            <img src="images/<?php echo htmlspecialchars($room['image']); ?>"
                                 alt="<?php echo htmlspecialchars($room['room_name']); ?>"
                                 class="room-image"
                                 loading="lazy">
                        <?php } else { ?>
                            <div class="room-image no-image">No Image Available</div>
                        <?php } ?>

                        <div class="room-name"><?php echo htmlspecialchars($room['room_name']); ?></div>
                        <div class="room-type"><?php echo htmlspecialchars($room['room_type']); ?></div>
                        <div class="room-info">Capacity: <?php echo (int) $room['capacity']; ?> person(s)</div>
                        <div class="room-info">Location: <?php echo htmlspecialchars($room['location']); ?></div>
                        <div class="room-price">Price: Rs. <?php echo number_format((float) $room['price_per_night']); ?> per night</div>
                        <div class="room-desc"><?php echo htmlspecialchars($room['description']); ?></div>

                        <?php if (strtolower($room['status']) == "available") { ?>
                            <span class="available">Available</span>
                        <?php } else { ?>
                            <span class="unavailable">Unavailable</span>
                        <?php } ?>

                        <span class="book-label">Book Now</span>

                    </div>
                </a>

            <?php } ?>

        </div>

        <?php if ($total_rooms > $limit) { ?>
            <div class="text-center mt-4">
                <a href="<?php echo htmlspecialchars($show_more_link); ?>" class="btn btn-dark">Show More</a>
            </div>
        <?php } ?>

    <?php } else { ?>

        <div class="no-results">
            No rooms matched your search. Try a different name, type or location.
        </div>

    <?php } ?>

</div>

<?php include "Footer.php"; ?>

</body>
</html>
<?php
$stmt->close();
$conn->close();
?>