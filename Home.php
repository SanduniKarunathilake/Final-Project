<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LEGACY sports academy</title>
    <link rel="stylesheet" href="styleh.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body>
    <?php
    session_start(); // Start the session at the very beginning
    ?>
    <header class="navbar section-content">
        <div class="nav-container">
            <ul><img src="image/2.png" width="80px" height="80px"></ul>
            <a href="#" class="nav-logo">
                <h2 class="logo-text">LEGACY</h2>
                <h3 class="logo-subtext">Sports Academy</h3>
            </a>

            <ul class="nav-menu">
                <button id="menu-close-button" class="fas fa-times"></button>
                <li class="nav-item">
                    <a href="home.php" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="about.html" class="nav-link">About</a>
                </li>
                <li class="nav-item">
                    <a href="sports.html" class="nav-link">Sports</a>
                </li>
                <li class="nav-item">
                    <a href="donations.html" class="nav-link">Donations</a>
                </li>

                <li class="nav-item">
                    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                        <a href="logout.php" class="nav-link">LOG OUT</a>
                    <?php else: ?>
                        <a href="loginselect.html" class="nav-link">LOG IN</a>
                    <?php endif; ?>
                </li>

            </ul>
            <button id="menu-open-button" class="fas fa-bars"></button>
        </div>
    </header>

    <main>
        <!--hero section-->
        <section class="hero-section">
            <div class="section-content">
                <div class="hero-details">
                    <h2 class="title">BE A LEGEND</h2>
                    <h3 class="subtitle">Beyond the Finish Line</h3>
                    <p class="description">Welcome to Legacy Sports Academy! Here, we nurture talent and strive for excellence. With top-notch coaching and facilities, we support you every step of the way.
                        Let's create a legacy of success together!</p>
                    <div class="buttons">
                        <?php if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true): ?>
                            <a href="register.html" class="button Register">Sign IN</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="hero-image-wrapper">
                    <img src="image/HOME2.png" alt="Hero" class="hero-image">
                </div>

            </div>
        </section>
    </main>
    <script src="scripth.js"></script>
</body>

</html>