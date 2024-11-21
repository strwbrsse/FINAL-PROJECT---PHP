<?php

require_once 'UserAuth.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? null;
    $email = $_POST['Email'] ?? null;
    $password = $_POST['Pass'] ?? null;

    $dbConfig = [
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'root',
        'dbname' => 'shotsafe_data'
    ];

    $UserAuth = new UserAuth($dbConfig);

    if ($action === 'signin') {
        $result = $UserAuth->authenticate($email, $password);
    } elseif ($action === 'register') {
    } else {
        $result = ["success" => false, "message" => "Invalid action"];
    }

    echo json_encode($result);

    $UserAuth->close();
}
