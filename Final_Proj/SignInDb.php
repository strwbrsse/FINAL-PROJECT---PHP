<?php
$host = 'localhost';
$db = 'shotsafe';
$user = 'root';
$pass = 'root';

$con = new mysqli($host, $user, $pass, $db);

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT user_password FROM users WHERE user_name = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            echo "Login successful! Welcome, " . htmlspecialchars($username);
        } else {
            echo "Incorrect password.";
        }
    } else {
        echo "Username not found.";
    }

    $stmt->close();
    $con->close();
}
?>