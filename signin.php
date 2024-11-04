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
                      <a href='index.html' style='text-decoration: none;'>
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
    } else {
        $stmt = $conn->prepare("SELECT user_password FROM users WHERE user_mail = ?");
        $stmt->bind_param("s", $user_mail);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            displayError("No account found with that email.");
        } else {
            $stmt->bind_result($hashed_password);
            $stmt->fetch();

            if (password_verify($user_password, $hashed_password)) {
                $loginSuccess = true;
                header("Location: loading.php?destination=main.html");
                exit();   
            } else {
                displayError("Incorrect password.");
            }
        }

        $stmt->close();
    }
}

$conn->close();
?>