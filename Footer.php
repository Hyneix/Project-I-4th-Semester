<!-- Footer.php - reusable site footer (plain HTML, no PHP needed)
     Use at the bottom of every page:  include "Footer.php";  -->

<!-- Bootstrap Icons (used only for the small contact icons) -->
<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
/* ============================================================
   SIMPLE FOOTER - dark gray background, light text
   ============================================================ */
.site-footer {
    background-color: #222222;
    color: #cccccc;
    margin-top: 50px;
    font-family: Arial, sans-serif;
}

/* Top part: 3 columns */
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

/* Footer links */
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

/* Bottom copyright bar */
.footer-bottom {
    border-top: 1px solid #444444;
    text-align: center;
    font-size: 13px;
    padding: 12px 0;
    color: #999999;
}

/* Dark mode: footer stays dark */
body.dark-mode .site-footer {
    background-color: #111111;
    color: #adb5bd;
}

/* Small screens: stack the columns */
@media (max-width: 700px) {
    .footer-main {
        grid-template-columns: 1fr;
        text-align: center;
    }
}
</style>

<!-- ===================== FOOTER ===================== -->
<footer class="site-footer">

    <div class="footer-main">

        <!-- 1. Website name + short description -->
        <div>
            <h5>Room Booking System</h5>
            <p>Find and book suitable rooms easily.</p>
        </div>

        <!-- 2. Quick links (same links as the header) -->
        <div class="footer-links">
            <h6>Quick Links</h6>
            <a href="index.php">Home</a>
            <a href="Search.php">Search</a>
            <a href="my_bookings.php">Bookings</a>
            <a href="UserProfile.php">Profile</a>
        </div>

        <!-- 3. Contact -->
        <div>
            <h6>Contact</h6>
            <p><i class="bi bi-envelope"></i> support@roombooking.com</p>
            <p><i class="bi bi-telephone"></i> +977-9800000000</p>
            <p><i class="bi bi-geo-alt"></i> Kathmandu, Nepal</p>
        </div>

    </div>

    <!-- Copyright bar -->
    <div class="footer-bottom">
        &copy; 2026 Room Booking System. All rights reserved.
    </div>

</footer>