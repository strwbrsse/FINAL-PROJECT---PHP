<?php
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "shotsafe";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$registrationSuccess = false;
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_name = trim($_POST['name']);
    $user_mail = trim($_POST['email']);
    $user_password = trim($_POST['pass']);
    $conPass = trim($_POST['conPass']);

    if (empty($user_name) || empty($user_mail) || empty($user_password)) {
        die("All fields are required");
    } elseif (!filter_var($user_mail, FILTER_VALIDATE_EMAIL)) {
        die("Invalid Email format!");
    } elseif(strlen($user_password) < 8) {
        die("Password too short!");
    }elseif ($user_password != $conPass) {
        die("Passwords didn't match!");
    } else {
        $stmt = $conn->prepare("SELECT user_mail FROM users WHERE user_mail = ?");
        $stmt->bind_param("s", $user_mail);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            die("Email already exists. Please use a different email.");
        } else {
            $hash_pass = password_hash($user_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (user_name, user_mail, user_password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $user_name, $user_mail, $hash_pass);

            if ($stmt->execute()) {
                $registrationSuccess = true;
                header("Location: loading.php?destination=index.html");
                exit();                
            } else {
                $errorMessage = "Error: " . $stmt->error;
            }
        }

        $stmt->close();
    }
}

$conn->close();
?>