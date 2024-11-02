<?php
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "shotsafe";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$loginSuccess = false;
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_name = trim($_POST['name']);
    $user_mail = trim($_POST['email']);
    $user_password = trim($_POST['pass']);

    if (empty($user_name) || empty($user_mail) || empty($user_password)) {
        die("All fields are required");
    } elseif (!filter_var($user_mail, FILTER_VALIDATE_EMAIL)) {
        die("Invalid Email format!");
    } else {
        $stmt = $conn->prepare("SELECT user_password FROM users WHERE user_mail = ?");
        $stmt->bind_param("s", $user_mail);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            die("No account found with that email.");
        } else {
            $stmt->bind_result($hashed_password);
            $stmt->fetch();

            if (password_verify($user_password, $hashed_password)) {
                $loginSuccess = true;
                header("Location: loading.php?destination=main.html");
                exit();   
            } else {
                die("Incorrect password.");
            }
        }

        $stmt->close();
    }
}

$conn->close();
?>