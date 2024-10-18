<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Register - Shotsafe</title>
        <link rel="stylesheet" type="text/css" href="style_r.css">
    </head>
    <body>
        <h2>Register for ShotSafe</h2>
        <form action="register.php" method="POST">
            <label>Username:</label><br>
            <input type="text" name="username" required><br>

            <label>Email:</label><br>
            <input type="email" name="email" required><br>

            <label>Password:</label><br>
            <input type="password" name="password" required><br>

            <button type="submit">Register</button>
        </form>
    </body>
</html>

<?
$conn = new mysqli('localhost', 'root', '', 'shotsafe');

if ($conn->connect_error){
    
}