<?php
$host = 'localhost';
$db = 'shotsafe';
$user = 'root';
$pass = 'root';

$con = new mysqli($host, $user, $pass, $db); // Using $con consistently

// Check the connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error); // Use $con, not $conn
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password != $confirm_password) {
        echo "Passwords didn't match";
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare the SQL statement
    $sql = "INSERT INTO users (user_name, user_mail, user_password) VALUES (?, ?, ?)";
    $stmt = $con->prepare($sql); // Use $con, not $conn
    $stmt->bind_param("sss", $username, $email, $hashed_password);
    
    // Execute and check for errors
    if ($stmt->execute()) {
        echo "Registration successful! <a href='SignIn.html'>Login here</a>";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $con->close();
}
?>
