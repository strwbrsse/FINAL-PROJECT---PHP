<?php
session_start();
require_once 'DB_Operations.php';
require_once 'DB_Connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['name_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not authenticated'
    ]);
    exit;
}

try {
    $dbConn = new DbConn([/* your config here */]);
    $SQL_Operations = new SQL_Operations($dbConn);

    $data = [
        'fname' => $_POST['fname'] ?? null,
        'mname' => $_POST['mname'] ?? null,
        'lname' => $_POST['lname'] ?? null,
        'email' => $_POST['email'] ?? null,
        'contact' => $_POST['contact'] ?? null,
        'birthday' => $_POST['birthday'] ?? null,
        'sex' => $_POST['sex'] ?? null,
        'civilstat' => $_POST['civilstat'] ?? null,
        'address' => $_POST['address'] ?? null
    ];

    $result = $SQL_Operations->updateProfile($_SESSION['name_id'], $data);
    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error updating profile: ' . $e->getMessage()
    ]);
}
?> 