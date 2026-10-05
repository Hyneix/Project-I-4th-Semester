<?php $base = isset($base) ? $base : "";  ?>

<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

.site-footer {
    background-color: #222222;
    color: #cccccc;
    margin-top: 50px;
    font-family: Arial, sans-serif;
}

.footer-main {
    width: 90%;
    max-width: 1200px;
    margin: auto;
    padding: 35px 0 20px 0;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 25px;
}

.footer-main h5 {
    color: #ffffff;
    font-size: 18px;
    margin-bottom: 10px;
}

.footer-main h6 {
    color: #ffffff;
    font-size: 15px;
    margin-bottom: 10px;
}

.footer-main p {
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 8px;
}




.footer-links a {
    display: block;
    color: #cccccc;
    text-decoration: none;
    font-size: 14px;
    margin-bottom: 6px;
}

.footer-links a:hover {
    color: #ffffff;
    text-decoration: underline;
}

.footer-bottom {
    border-top: 1px solid #444444;
    text-align: center;
    font-size: 13px;
    padding: 12px 0;
    color: #999999;
}

body.dark-mode .site-footer {
    background-color: #111111;
    color: #adb5bd;
}

@media (max-width: 700px) {
    .footer-main {
        grid-template-columns: 1fr;
        text-align: center;
    }
}
</style>

<footer class="site-footer">

    <div class="footer-main">

        <div>
            <h5>Room Booking System</h5>
            <p>Find and book suitable rooms easily.</p>
        </div>

        <div class="footer-links">
            <h6>Quick Links</h6>
            <a href="<?php echo $base; ?>index.php">Home</a>
            <a href="<?php echo $base; ?>Search.php">Search</a>
            <a href="<?php echo $base; ?>my_bookings.php">Bookings</a>
            <a href="<?php echo $base; ?>UserProfile.php">Profile</a>
        </div>

        <div>
            <h6>Contact</h6>
            <p><i class="bi bi-envelope"></i> support@roombooking.com</p>
            <p><i class="bi bi-telephone"></i> +977-9800000000</p>
            <p><i class="bi bi-geo-alt"></i> Kathmandu, Nepal</p>
        </div>

    </div>

    <div class="footer-bottom">
        &copy; 2026 Room Booking System. All rights reserved.
    </div>

</footer>