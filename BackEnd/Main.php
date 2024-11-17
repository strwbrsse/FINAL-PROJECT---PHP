<?php 
require_once 'UserAuth.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $email = $_POST['Email'];
    $password = $_POST['Pass'];

    $dbConfig = [
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'root',
        'database' => 'shotsafe_db'
    ];

    $UserAuth = new UserAuth($dbConfig);
    $result = $UserAuth->authenticate($email, $password);

    echo json_encode($result);

    $UserAuth->close();
}
?>