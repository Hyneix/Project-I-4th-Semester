<?php
// if (session_status() == PHP_SESSION_NONE) {
//     session_start();
// }

$currentPage = strtolower(basename($_SERVER['PHP_SELF']));
?>

<style>

.site-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;                  
    gap: 10px;
    padding: 12px 25px;
    background: #fff;
    border-bottom: 1px solid #e5e5e5;
}

.left-section {
    display: flex;
    align-items: center;
}

.left-section .logo img {
    height: 35px;
}

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

.nav-section a:hover,
.nav-section a.active {
    background-color: #333;
    color: white;
}

.right-section {
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn-header {
    padding: 7px 14px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 14px;
}

.btn-header-outline {
    border: 1px solid #333;
    color: #333;
    background: #fff;
}

.btn-header-outline:hover {
    background: #333;
    color: #fff;
}

.btn-header-dark {
    border: 1px solid #333;
    color: #fff;
    background: #333;
}

.btn-header-dark:hover {
    background: #555;
    border-color: #555;
}

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

@media (max-width: 768px) {
    .site-header {
        padding: 10px 15px;
    }

    .nav-section {
        order: 3;                     
        width: 100%;
        justify-content: center;
    }
}

</style>


<header class="site-header">

    <div class="left-section">

        <div class="logo">
            <a href="index.php">
                <img src="images/logo.png" alt="Logo">
            </a>
        </div>

    </div>

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
           class="<?php echo $currentPage == 'booking.php' ? 'active' : ''; ?>">
           Bookings
        </a>

        <a href="ContactUs.php"
           class="<?php echo $currentPage == 'contactus.php' ? 'active' : ''; ?>">
           Contact Us
        </a>

        <a href="UserProfile.php"
           class="<?php echo $currentPage == 'userprofile.php' ? 'active' : ''; ?>">
           Profile
        </a>

        

    </nav>

    <div class="right-section">

        <?php if (isset($_SESSION['user_id'])): ?>

            <?php
            
            $firstLetter = mb_strtoupper(mb_substr($_SESSION['full_name'], 0, 1));
            ?>

            <div class="user-icon">
                <?php echo htmlspecialchars($firstLetter); ?>
            </div>

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

    </div>

</header>