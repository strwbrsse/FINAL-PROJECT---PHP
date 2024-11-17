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
        'dbname' => 'shotsafe_db'
    ];

    $UserAuth = new UserAuth($dbConfig);
    
    if ($action === 'signin') {
        $result = $UserAuth->authenticate($email, $password);
    } else {
        $result = ["success" => false, "message" => "Invalid action"];
    }

    echo json_encode($result);

    $UserAuth->close();
}   
?>