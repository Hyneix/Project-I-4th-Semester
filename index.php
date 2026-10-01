<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Room Booking System - Find a room in seconds</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

<style>
/* ---------- Basic Page ---------- */
body {
    font-family: Arial, sans-serif;
    background: #f8f9fa;
    color: #212529;
}

/* ---------- Hero Section ---------- */
.hero {
    min-height: 400px;
    background-image:
        linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.45)),
        url('images/room1.svg');
    background-size: cover;
    background-position: center;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 40px 20px;
}

.hero h1 {
    font-size: 42px;
    font-weight: bold;
}

.hero p {
    font-size: 18px;
    margin-bottom: 20px;
}

/* ---------- Search Form ---------- */
.search-form-box {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-top: -40px;
    position: relative;
    z-index: 2;
}

.search-form-box label {
    font-size: 13px;
    font-weight: bold;
    color: #555;
}

/* ---------- Footer ---------- */
footer {
    margin-top: 40px;
    padding: 20px 0;
    text-align: center;
    color: #6c757d;
    font-size: 14px;
}

</style>
</head>

<body>

<?php include "header.php"; ?>

<!-- Hero Section -->
<section class="hero">
    <div>
        <h1>Find a room<br>in seconds</h1>
        <p>Fast one-page booking for Kathmandu, Pokhara, Chitwan and more</p>
        <a href="#search-box" class="btn btn-primary btn-lg">Search</a>
    </div>
</section>

<!-- Search Form -->
<div class="container">
    <div class="search-form-box" id="search-box">
        <form action="search.php" method="GET" onsubmit="return validateSearchForm();">

            <div class="row g-3 align-items-end">

                <div class="col-md-3">
                    <label for="location">Location</label>
                    <input
                        type="text"
                        class="form-control"
                        id="location"
                        name="location"
                        placeholder="City or hotel">
                </div>

                <div class="col-md-3">
                    <label for="checkin">Check-in</label>
                    <input
                        type="date"
                        class="form-control"
                        id="checkin"
                        name="checkin">
                </div>

                <div class="col-md-3">
                    <label for="checkout">Check-out</label>
                    <input
                        type="date"
                        class="form-control"
                        id="checkout"
                        name="checkout">
                </div>

                <div class="col-md-2">
                    <label for="guests">Guests</label>
                    <input
                        type="number"
                        class="form-control"
                        id="guests"
                        name="guests"
                        min="1"
                        placeholder="Max 4 Guests">
                </div>

                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        Search
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<footer>
    &copy; 2026 Room Booking System. All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>