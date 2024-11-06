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

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $user_name = trim($_POST['name']);
        $user_mail = trim($_POST['email']);
        $user_password = trim($_POST['pass']);
        $conPass = trim($_POST['conPass']);
    
        function displayError($message) {
            die("<style>
                    .return {
                        margin-top: 20px;
                        padding: 10px 20px;
                        background-color: white;
                        color: black;
                        border: none;
                        border-radius: 5px;
                        cursor: pointer;
                        font-size: 16px;
                        font-weight: bold;
                        transition: background-color 0.3s, color 0.3s; /* Smooth transition for hover effect */
                    }
                    .return:hover {
                        background-color: #e1f0f0;
                        color: rgb(54, 54, 54);
                    }
                  </style>
                  <div style='display: flex; justify-content: center; align-items: center; height: 100vh;'>
                      <div style='font-family: Montserrat, sans-serif; background-color: #c9e3df; padding: 20px; display: inline-block; text-align: center; border-radius: 8px;'>
                          <h2>$message</h2>
                          <a href='signup.html' style='text-decoration: none;'>
                              <button class='return'>
                                  Return
                              </button>
                          </a>
                      </div>
                  </div>");
        }
    
        if (empty($user_name) || empty($user_mail) || empty($user_password)) {
            displayError("All fields are required");
        } elseif (!filter_var($user_mail, FILTER_VALIDATE_EMAIL)) {
            displayError("Invalid Email format!");
        } elseif (strlen($user_password) < 8) {
            displayError("Password too short!");
        } elseif ($user_password != $conPass) {
            displayError("Passwords didn't match!");
        } else {
            $stmt = $conn->prepare("SELECT user_mail FROM users WHERE user_mail = ?");
            $stmt->bind_param("s", $user_mail);
            $stmt->execute();
            $stmt->store_result();
        
            if ($stmt->num_rows > 0) {
                displayError("Email already exists. Please use a different email.");
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
}

$conn->close();
?>