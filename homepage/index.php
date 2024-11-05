<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ShotSafe | Secure and Easy Vaccination Management System</title>
        <link rel="stylesheet" href="styles.css"> 
    </head>
    <body>
        <!-- HEADER -->
        <header>
            <div class="container">
                <h1>ShotSafe</h1>
                <p>Your Secure and easy vaccination management system</p>
            </div>
        </header>
        <!-- Navigation -->
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="features.php">Features</a></li>
                <li><a href="contact.php">Contact</a></li>
                <?php if (isset($_SESSION['user'])): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="signin.php">Login</a></li>
                    <li><a href="signup.php">Register</a></li>
                    <?php endif; ?>
            </ul>
        </nav>

        <!-- Hero Section --> 
        <section class="hero">
            <div class="hero-text">
                <h2>Welcome to ShotSafe</h2>
                <p>Manage, Track, and Stay updated on vaccinations with ease and security.</p>
                <a href="signup.php" class="btn">Get Started</a>
            </div>
        </section>

        <!-- About Section --> 
        <section class="about">
            <div class="container">
                <h2>About ShotSafe</h2>
                <p>ShotSafe is a digital platform designed to help healthcare providers and individuals manage vaccination records securely and efficiently. Our mission is to make vaccination tracking accessible and safe for everyone.</p>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features">
            <div class="container">
                <h2>Features</h2>
            <div class="feature-item">
                <h3>Secure Data Management</h3>
                <p>We use state-of-the-art encryption to ensure your data is protected at all times.</p>
            </div>
            <div class="feature-item">
                <h3>Easy Access</h3>
                <p>Access your vaccination records anytime, anywhere with our user-friendly portal.</p>
            </div>
                <div class="feature-item">
                <h3>Reminders & Notifications</h3>
                <p>Stay on top of upcoming vaccinations with our automated reminder system.</p>
            </div>
            </div>
        </section>

        <!-- Footer Section -->
        <footer>
            <div class="container">
            <p>&copy; 2024 ShotSafe. All rights reserved.</p>
            </div>
        </footer>
    </body>
</html>