<?php
// index.php - homepage
// Shows: hero, search, popular destinations, rooms from the database,
//        features, newsletter CTA, footer.

include "dbconnection.php";

// Total rooms (for the hero subtitle)
$room_count = 0;
$count_res = $conn->query("SELECT COUNT(*) FROM rooms");
if ($count_res) {
    $room_count = (int) $count_res->fetch_row()[0];
}

// A few rooms for the "Rooms loved by guests" section
$popular_rooms = $conn->query(
    "SELECT room_id, room_name, room_type, capacity, location, image, status
     FROM rooms
     ORDER BY room_id
     LIMIT 5"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Room Booking System - Find a room in seconds</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
/* =========================================================
   BASIC PAGE
   ========================================================= */
body {
    font-family: Arial, sans-serif;
    background: #f5f5f5;
    color: #222222;
}

.section-title {
    font-size: 22px;
    font-weight: bold;
    margin: 40px 0 18px;
}

/* =========================================================
   HERO (Screenshot 1)
   ========================================================= */
.hero {
    min-height: 440px;
    background-color: #333333;   /* shown only if the image is missing */
    background-image:
        linear-gradient(rgba(0, 0, 0, 0.45), rgba(0, 0, 0, 0.45)),
        url('images/BgImages.jpg');
    background-size: cover;
    background-position: center;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 60px 20px 110px;   /* extra bottom space for the search bar */
}

.hero h1 {
    font-size: 42px;
    font-weight: bold;
}

/* soft shadow keeps the white text readable on bright parts of the photo */
.hero h1,
.hero .hero-sub,
.hero .hero-count {
    text-shadow: 0 1px 4px rgba(0, 0, 0, 0.6);
}

.hero .hero-sub {
    font-size: 17px;
    color: #e0e0e0;
    margin-bottom: 6px;
}

.hero .hero-count {
    font-size: 15px;
    color: #d0d0d0;
    margin-bottom: 22px;
}

/* =========================================================
   SEARCH BAR (Screenshot 1 - white bar overlapping the hero)
   ========================================================= */
.search-bar-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
    padding: 14px 20px;
    margin-top: -60px;
    position: relative;
    z-index: 2;
}

.search-bar-card label {
    font-size: 12px;
    font-weight: bold;
    color: #555;
    margin-bottom: 2px;
}

/* inputs have no visible box, like in the screenshot */
.search-bar-card .form-control {
    border: none;
    padding-left: 0;
    font-size: 14px;
    box-shadow: none;
    background: transparent;
}

/* vertical divider between fields (desktop only) */
.search-divider {
    border-left: 1px solid #e0e0e0;
    padding-left: 18px;
}

/* round blue arrow button */
.search-circle-btn {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: #0d6efd;
    color: #fff;
    border: none;
    font-size: 20px;
}

.search-circle-btn:hover {
    background: #0b5ed7;
    color: #fff;
}

/* =========================================================
   POPULAR DESTINATIONS (Screenshots 1 + 2 - masonry grid)
   ========================================================= */
.dest-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    grid-auto-rows: 190px;
    gap: 16px;
}

.dest-card {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    display: block;
    text-decoration: none;
}

.dest-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: 0.2s;
}

.dest-card:hover img {
    opacity: 0.9;
}

/* card that spans two rows (tall image) */
.dest-tall {
    grid-row: span 2;
}

.dest-label {
    position: absolute;
    left: 14px;
    bottom: 14px;
    background: rgba(255, 255, 255, 0.92);
    color: #222;
    font-size: 13px;
    font-weight: bold;
    padding: 5px 14px;
    border-radius: 20px;
}

/* =========================================================
   ROOM CARDS (Screenshot 2 - "Hotels loved by guests")
   ========================================================= */
.room-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 18px;
}

.room-card {
    background: #fff;
    border: 1px solid #e2e2e2;
    border-radius: 12px;
    overflow: hidden;
    display: block;
    color: #222;
    text-decoration: none;
}

.room-card:hover {
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
}

.room-img-wrap {
    position: relative;
}

.room-img-wrap img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    display: block;
}

/* small status badge, top left */
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

/* heart icon, top right (visual only) */
.room-heart {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.85);
    color: #555;
    text-align: center;
    line-height: 28px;
    font-size: 13px;
}

.room-body {
    padding: 12px 14px 14px;
}

.room-name {
    font-size: 15px;
    font-weight: bold;
}

.room-loc {
    font-size: 12px;
    color: #888;
    margin-bottom: 10px;
}

.room-foot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
    font-weight: bold;
}

.room-foot .arrow {
    color: #0d6efd;
}

/* =========================================================
   FEATURES ("Why book with us") - simple 3 icon cards
   ========================================================= */
.feature-card {
    background: #fff;
    border: 1px solid #e2e2e2;
    border-radius: 12px;
    padding: 24px 18px;
    text-align: center;
    height: 100%;
}

.feature-icon {
    width: 52px;
    height: 52px;
    margin: 0 auto 12px;
    border-radius: 50%;
    background: #e7f0ff;
    color: #0d6efd;
    font-size: 22px;
    line-height: 52px;
}

.feature-card h6 {
    font-weight: bold;
    font-size: 15px;
}

.feature-card p {
    font-size: 13px;
    color: #777;
    margin: 0;
}

/* =========================================================
   NEWSLETTER / CTA (Screenshot 2 - "Pssst!" band)
   ========================================================= */
.newsletter-box {
    background: #fff;
    border: 1px solid #e2e2e2;
    border-radius: 12px;
    padding: 26px;
    margin: 40px 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
}

.newsletter-box .big-icon {
    font-size: 34px;
    color: #0d6efd;
    margin-right: 16px;
}

.newsletter-box h5 {
    font-weight: bold;
    font-size: 16px;
    margin-bottom: 4px;
}

.newsletter-box p {
    font-size: 13px;
    color: #777;
    margin: 0;
}

.btn-outline-blue {
    border: 1px solid #0d6efd;
    color: #0d6efd;
    background: #fff;
    border-radius: 20px;
    padding: 8px 20px;
    font-size: 14px;
    text-decoration: none;
}

.btn-outline-blue:hover {
    background: #0d6efd;
    color: #fff;
}

/* =========================================================
   DARK MODE support for the new sections
   ========================================================= */
body.dark-mode .search-bar-card,
body.dark-mode .room-card,
body.dark-mode .feature-card,
body.dark-mode .newsletter-box {
    background: #2b3035;
    border-color: #495057;
    color: #e0e0e0;
}

body.dark-mode .room-name {
    color: #f1f1f1;
}

body.dark-mode .room-loc,
body.dark-mode .feature-card p,
body.dark-mode .newsletter-box p {
    color: #adb5bd;
}

body.dark-mode .section-title {
    color: #f1f1f1;
}

body.dark-mode .search-bar-card label {
    color: #ccc;
}

body.dark-mode .search-bar-card .form-control {
    color: #e0e0e0;
}

body.dark-mode .search-bar-card .form-control::placeholder {
    color: #888;
}

/* =========================================================
   RESPONSIVE
   ========================================================= */
@media (max-width: 992px) {
    .room-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .hero h1 {
        font-size: 30px;
    }

    /* destinations: 2 columns, no tall cards */
    .dest-grid {
        grid-template-columns: repeat(2, 1fr);
        grid-auto-rows: 150px;
    }

    .dest-tall {
        grid-row: span 1;
    }

    /* search fields stack, dividers hidden, button full width */
    .search-divider {
        border-left: none;
        padding-left: 0;
    }

    .room-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .room-grid {
        grid-template-columns: 1fr;
    }
}
</style>
</head>

<body>

<?php include "header.php"; ?>

<!-- ============ HERO (from Screenshot 1) ============ -->
<section class="hero">
    <div>
        <h1>Find a room<br>in seconds</h1>
        <p class="hero-sub">Fast and simple room booking for Kathmandu, Pokhara, Chitwan and more</p>
        <p class="hero-count"><?php echo $room_count; ?> rooms around the country are waiting for you!</p>
    </div>
</section>

<!-- ============ SEARCH BAR (from Screenshot 1) ============ -->
<div class="container">
    <div class="search-bar-card">
        <form action="Search.php" method="GET" onsubmit="return validateSearchForm();">

            <div class="row g-3 align-items-center">

                <!-- Search.php reads this value as "location"
                     (it searches room name, room type and location) -->
                <div class="col-md-6">
                    <label for="location">Room name</label>
                    <input type="text" class="form-control" id="location"
                           name="location" placeholder="e.g. Deluxe, Kathmandu">
                </div>

                <div class="col-md-4 search-divider">
                    <label for="guests">Guests</label>
                    <input type="number" class="form-control" id="guests"
                           name="guests" min="1" placeholder="1">
                </div>

                <div class="col-md-2 text-md-end d-grid">
                    <button type="submit" class="search-circle-btn" title="Search">
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<div class="container">

    <!-- ============ POPULAR DESTINATIONS (Screenshots 1 + 2) ============ -->
    <h2 class="section-title">Popular destinations</h2>

    <div class="dest-grid">

        <a href="Search.php?location=Kathmandu" class="dest-card dest-tall">
            <img src="images/kathmandu-valley.jpg" alt="Kathmandu">
            <span class="dest-label">Kathmandu</span>
        </a>

        <a href="Search.php?location=Pokhara" class="dest-card">
            <img src="images/Colorful-boats-in-Fewa-Taal-Pokhara.jpg" alt="Pokhara">
            <span class="dest-label">Pokhara</span>
        </a>

        <a href="Search.php?location=Chitwan" class="dest-card dest-tall">
            <img src="images/indian-elephant-chitwan-nepal.jpg" alt="Chitwan">
            <span class="dest-label">Chitwan</span>
        </a>

        <a href="Search.php?location=Nagarkot" class="dest-card">
            <img src="images/nagarkot.jpg" alt="Nagarkot">
            <span class="dest-label">Nagarkot</span>
        </a>

        <a href="Search.php?location=Bhaktapur" class="dest-card">
            <img src="images/Kathmandu-Bhaktapur.jpg" alt="Bhaktapur">
            <span class="dest-label">Bhaktapur</span>
        </a>

        <a href="Search.php?location=Lumbini" class="dest-card">
            <img src="images/BRP_Lumbini_Mayadevi_temple.jpg" alt="Lumbini">
            <span class="dest-label">Lumbini</span>
        </a>

    </div>

    <!-- ============ ROOMS LOVED BY GUESTS (from Screenshot 2) ============ -->
    <h2 class="section-title">Rooms loved by guests</h2>

    <?php if ($popular_rooms && $popular_rooms->num_rows > 0) { ?>

        <div class="room-grid">

            <?php while ($room = $popular_rooms->fetch_assoc()) { ?>

                <a href="Booking.php?room_id=<?php echo (int) $room['room_id']; ?>" class="room-card">

                    <div class="room-img-wrap">

                        <?php if (!empty($room['image'])) { ?>
                            <img src="images/<?php echo htmlspecialchars($room['image']); ?>"
                                 alt="<?php echo htmlspecialchars($room['room_name']); ?>">
                        <?php } else { ?>
                            <img src="images/room1.svg" alt="Room">
                        <?php } ?>

                        <?php if (strtolower($room['status']) == 'available') { ?>
                            <span class="room-badge badge-available">Available</span>
                        <?php } else { ?>
                            <span class="room-badge badge-unavailable">Unavailable</span>
                        <?php } ?>

                        <span class="room-heart"><i class="bi bi-heart"></i></span>

                    </div>

                    <div class="room-body">
                        <div class="room-name"><?php echo htmlspecialchars($room['room_name']); ?></div>
                        <div class="room-loc">
                            <?php echo htmlspecialchars($room['room_type']); ?>
                            &middot;
                            <?php echo htmlspecialchars($room['location']); ?>
                        </div>
                        <div class="room-foot">
                            <span>Capacity: <?php echo (int) $room['capacity']; ?></span>
                            <span class="arrow"><i class="bi bi-chevron-right"></i></span>
                        </div>
                    </div>

                </a>

            <?php } ?>

        </div>

    <?php } else { ?>

        <p class="text-muted">No rooms available right now. Please check back soon.</p>

    <?php } ?>

    <!-- ============ WHY BOOK WITH US (extra info section) ============ -->
    <h2 class="section-title">Why book with us</h2>

    <div class="row g-3">

        <div class="col-md-4">
            <div class="feature-card">
                <div class="feature-icon"><i class="bi bi-search"></i></div>
                <h6>Easy Search</h6>
                <p>Find rooms by city, type or number of guests in seconds.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="feature-card">
                <div class="feature-icon"><i class="bi bi-calendar-check"></i></div>
                <h6>Instant Booking</h6>
                <p>Book a suitable room online with just a few clicks.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="feature-card">
                <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                <h6>Trusted Rooms</h6>
                <p>Every listed room is verified and ready for your stay.</p>
            </div>
        </div>

    </div>

    <!-- ============ NEWSLETTER CTA (from Screenshot 2) ============ -->
    <div class="newsletter-box">

        <div class="d-flex align-items-center">
            <i class="bi bi-hand-holding-heart big-icon"></i>
            <div>
                <h5>Pssst!</h5>
                <p>Do you want to get secret offers and best prices for amazing stays?<br>
                   Sign up to join our Travel Club!</p>
            </div>
        </div>

        <a href="register.php" class="btn-outline-blue">Sign up for newsletter</a>

    </div>

</div>

<?php include "Footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
/* Simple search validation: guests must be at least 1 (if typed). */
function validateSearchForm() {
    var guests = document.getElementById("guests").value;

    if (guests !== "" && parseInt(guests) < 1) {
        alert("Guests must be at least 1.");
        return false;
    }

    return true;
}
</script>

</body>
</html>
<?php
$conn->close();
?>