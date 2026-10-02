<?php
// if (session_status() == PHP_SESSION_NONE) {
//     session_start();
// }

// Name of the page that is open now (example: "userprofile.php")
$currentPage = strtolower(basename($_SERVER['PHP_SELF']));
?>

<style>
/* ============================================================
   SIMPLE FLEXBOX HEADER
   Logo -> LEFT | Navigation -> MIDDLE | User + Toggle -> RIGHT
   ============================================================ */
.site-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;                  /* wraps on small screens */
    gap: 10px;
    padding: 12px 25px;
    background: #fff;
    border-bottom: 1px solid #e5e5e5;
}

/* ---------- LEFT: logo ---------- */
.left-section {
    display: flex;
    align-items: center;
}

.left-section .logo img {
    height: 35px;
}

/* ---------- MIDDLE: navigation links ---------- */
.nav-section {
    display: flex;
    align-items: center;
    gap: 8px;
}

.nav-section a {
    padding: 8px 14px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 15px;
    color: #333;
}

/* Hover and active page: dark background, white text */
.nav-section a:hover,
.nav-section a.active {
    background-color: #333;
    color: white;
}

/* ---------- RIGHT: user avatar / login buttons + theme toggle ---------- */
.right-section {
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Login / Register / Logout buttons */
.btn-header {
    padding: 7px 14px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 14px;
}

/* White button with dark border (Login, Logout) */
.btn-header-outline {
    border: 1px solid #333;
    color: #333;
    background: #fff;
}

.btn-header-outline:hover {
    background: #333;
    color: #fff;
}

/* Dark button (Register) */
.btn-header-dark {
    border: 1px solid #333;
    color: #fff;
    background: #333;
}

.btn-header-dark:hover {
    background: #555;
    border-color: #555;
}

/* Round user icon that shows the first letter of the name */
.user-icon {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: #333;
    color: #fff;
    font-weight: bold;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Theme toggle (Bootstrap switch, made gray instead of blue) */
.header-toggle .form-check {
    margin: 0;
}

.header-toggle .form-check-input {
    cursor: pointer;
}

.header-toggle .form-check-input:checked {
    background-color: #333;
    border-color: #333;
}

.header-toggle .form-check-input:focus {
    box-shadow: none;
    border-color: #333;
}

/* ---------- Small screens: navigation goes under the logo row ---------- */
@media (max-width: 768px) {
    .site-header {
        padding: 10px 15px;
    }

    .nav-section {
        order: 3;                     /* move below logo and right section */
        width: 100%;
        justify-content: center;
    }
}

/* ============================================================
   DARK MODE
   ============================================================ */
body.dark-mode {
    background: #212529;
    color: white;
}

body.dark-mode .site-header {
    background: #1b1e21;
    border-bottom-color: #343a40;
}

body.dark-mode .nav-section a {
    color: #e0e0e0;
}

body.dark-mode .nav-section a:hover,
body.dark-mode .nav-section a.active {
    background-color: #e0e0e0;
    color: #212529;
}

body.dark-mode .btn-header-outline {
    border-color: #adb5bd;
    color: #e0e0e0;
    background: transparent;
}

body.dark-mode .btn-header-outline:hover {
    background: #e0e0e0;
    color: #212529;
}

body.dark-mode .btn-header-dark {
    background: #e0e0e0;
    border-color: #e0e0e0;
    color: #212529;
}

body.dark-mode .btn-header-dark:hover {
    background: #bbbbbb;
}

body.dark-mode .user-icon {
    background: #e0e0e0;
    color: #212529;
}

body.dark-mode .form-select {
    background-color: #343a40;
    color: white;
    border-color: #495057;
}

/* Light arrow for dropdown lists in dark mode */
body.dark-mode .form-select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23dee2e6' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
}
</style>

<!-- Simple flexbox header:
     LEFT = logo | MIDDLE = navigation | RIGHT = user + toggle -->
<header class="site-header">

    <!-- LEFT SECTION: logo -->
    <div class="left-section">

        <div class="logo">
            <a href="index.php">
                <img src="images/logo.png" alt="Logo">
            </a>
        </div>

    </div>

    <!-- MIDDLE SECTION: navigation links (active page is highlighted) -->
    <nav class="nav-section">

        <a href="index.php"
           class="<?php echo $currentPage == 'index.php' ? 'active' : ''; ?>">
           Home
        </a>

        <a href="Search.php"
           class="<?php echo $currentPage == 'search.php' ? 'active' : ''; ?>">
           Search
        </a>

        <a href="Booking.php"
           class="<?php echo $currentPage == 'bookings.php' ? 'active' : ''; ?>">
           Bookings
        </a>

        <a href="UserProfile.php"
           class="<?php echo $currentPage == 'userprofile.php' ? 'active' : ''; ?>">
           Profile
        </a>

    </nav>

    <!-- RIGHT SECTION: user avatar / login buttons + theme toggle -->
    <div class="right-section">

        <?php if (isset($_SESSION['user_id'])): ?>

            <?php
            // First letter of the logged-in user's name
            // (set in login.php: $_SESSION['full_name'])
            $firstLetter = mb_strtoupper(mb_substr($_SESSION['full_name'], 0, 1));
            ?>

            <!-- User avatar (replaces "Hi, <name>") -->
            <div class="user-icon">
                <?php echo htmlspecialchars($firstLetter); ?>
            </div>

            <!-- Logout is shown ONLY on the profile page -->
            <?php if ($currentPage == 'userprofile.php'): ?>
                <a href="logout.php" class="btn-header btn-header-outline">
                    Logout
                </a>
            <?php endif; ?>

        <?php else: ?>
            <a href="login.php" class="btn-header btn-header-outline">
                Login
            </a>
            <a href="register.php" class="btn-header btn-header-dark">
                Register
            </a>
        <?php endif; ?>

        <!-- THEME TOGGLE (far right) -->
        <div class="header-toggle">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="themeSwitch">
            </div>
        </div>

    </div>

</header>

<script>
/* Light/Dark theme toggle - shared by every page that includes header.php.
   ONE localStorage key ("theme") is used on index.php, Search.php
   and UserProfile.php, so the choice is remembered everywhere. */

// 1. Read the saved theme when the page loads and apply it
const savedTheme = localStorage.getItem("theme");

if (savedTheme === "dark") {
    document.body.classList.add("dark-mode");
    document.getElementById("themeSwitch").checked = true;
}

// 2. When the toggle is clicked, change the theme and save the choice
document.getElementById("themeSwitch").addEventListener("change", function() {
    if (this.checked) {
        document.body.classList.add("dark-mode");
        localStorage.setItem("theme", "dark");
    } else {
        document.body.classList.remove("dark-mode");
        localStorage.setItem("theme", "light");
    }
});
</script>