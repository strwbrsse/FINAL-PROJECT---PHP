<?php
session_start();

//Redirect to login page if the user is not logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

//Get user's name for welcome message
$userName = $_SESSION['name'];
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=deice-width, initial-scale=1.0">
        <title>Welcome to ShotSafe</title>
        <style>::after
        body { font-family: Arial, sans-serif; background-color: #f4f6f8; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .welcome-container { text-align: center; padding: 40px; background-color: #fff; box-shadow: 0 0 15px rgba(0, 0, 0, 0.1); border-radius: 8px; width: 100%; max-width: 600px; }
        h1 { color: #4CAF50; }
        .welcome-message { font-size: 1.2em; margin: 20px 0; }
        .quick-links { margin-top: 30px; }
        .quick-links a { display: inline-block; margin: 10px; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px; }
        .quick-links a:hover { background-color: #45a049; }
        </style>
    </head>
    <body>
        <div class="welcome-container">
            <h1>Welcome, <?php echo htmlspecialchars($userName); ?>!</h1>
            <p class="welcome-message">Welcome to ShotSafe - your secure and easy vaccination management system.</p>

            <div class="quick-links">
                <h2>Quick Links</h2>
                <a href="dashboard.php">Dashboard</a>
                <a href="appointments.php">Manage Appointments</a>
                <a href="inventory.php">Vaccine Inventory</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </body>
</html>