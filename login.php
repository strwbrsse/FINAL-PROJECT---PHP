<?php
session_start();

//Database Connection
$conn = new mysqli('localhost', 'root', '', 'shotsafe');

//Checking of Connection
if ($conn->connect_error){
    die("Connection Failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    //Retrieving user data base on the email
    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");

    if ($result->num_rows > 0){
        $user = $result->fetch_assoc();

        //Verifying the password
        if (password_verify($password, $user['password'])){
            //Password is correct, start a session
            $_SESSION['username'] = $user['username'];
            $_SESSION['loggedin'] = true;
            header("Location: dashboard.php");
        } else {
            echo "Invalid email or password.";
        }
    } else {
        echo "User not found.";
    }
}

$conn->close();
?>
