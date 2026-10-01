<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Name of the page that is open now (example: "userprofile.php")
$currentPage = strtolower(basename($_SERVER['PHP_SELF']));
?>

<style>
/* ============================================================
   SIMPLE FLEXBOX HEADER
   Logo -> LEFT | Navigation -> MIDDLE | Toggle -> RIGHT
   ============================================================ */
.site-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 25px;               /* small margin from screen edges */
    background: #fff;
}

/* ---------- LEFT: logo ---------- */
.left-section {
    display: flex;
    align-items: center;
}

/* Logo (slightly larger text/size) */
.left-section .logo img {
    height: 35px;
}

/* ---------- MIDDLE: navigation links ---------- */
.nav-section {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Navigation links look like simple buttons */
.nav-section a {
    padding: 8px 12px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 16px;                  /* readable text size */
    color: #333;
    transition: 0.2s;                 /* smooth hover effect */
}

/* Hover: highlighted button */
.nav-section a:hover {
    background-color: #333;
    color: white;
}

/* Active page: stays highlighted without hovering */
.nav-section a.active {
    background-color: #333;
    color: white;
}

/* ---------- RIGHT: user avatar / login buttons + theme toggle ---------- */
.right-section {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-left: 30px;                /* reasonable gap before the toggle */
}

/* Login / Register / Logout buttons */
.btn-header {
    padding: 8px 14px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 15px;
    transition: 0.2s;
}

.btn-outline {
    border: 1px solid #0d6efd;
    color: #0d6efd;
    background: #fff;
}

.btn-outline:hover {
    background: #0d6efd;
    color: #fff;
}

.btn-outline-secondary {
    border: 1px solid #6c757d;
    color: #6c757d;
    background: #fff;
}

.btn-outline-secondary:hover {
    background: #6c757d;
    color: #fff;
}

.btn-primary {
    background: #0d6efd;
    color: #fff;
    border: 1px solid #0d6efd;
}

.btn-primary:hover {
    background: #0b5ed7;
}

/* Round user icon that shows the first letter of the name */
.user-icon {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: #0d6efd;
    color: #fff;
    font-weight: bold;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Theme toggle sits inside right-section (far right) */
.header-toggle {
    margin-left: 10px;
}

/* ============================================================
   DARK MODE - anchor tags and header also toggle with the theme
   ============================================================ */
body.dark-mode {
    background: #212529;
    color: white;
}

body.dark-mode .site-header {
    background: #1b1e21;
}

/* Navigation anchors in dark mode */
body.dark-mode .nav-section a {
    color: #e0e0e0;
}

body.dark-mode .nav-section a:hover {
    background-color: #0d6efd;
    color: white;
}

body.dark-mode .nav-section a.active {
    background-color: #0d6efd;
    color: white;
}

/* Login / Register / Logout buttons in dark mode */
body.dark-mode .btn-outline {
    border-color: #6ea8fe;
    color: #6ea8fe;
    background: transparent;
}

body.dark-mode .btn-outline:hover {
    background: #0d6efd;
    color: #fff;
}

body.dark-mode .btn-outline-secondary {
    border-color: #adb5bd;
    color: #adb5bd;
    background: transparent;
}

body.dark-mode .btn-outline-secondary:hover {
    background: #6c757d;
    color: #fff;
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

        <a href="#">Bookings</a>

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
                <a href="logout.php" class="btn-header btn-outline-secondary">
                    Logout
                </a>
            <?php endif; ?>

        <?php else: ?>
            <a href="login.php" class="btn-header btn-outline">
                Login
            </a>
            <a href="register.php" class="btn-header btn-primary">
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