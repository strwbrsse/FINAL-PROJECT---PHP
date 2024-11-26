<?php

require_once 'UserAuth.php';
require_once 'Register.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // sign in
    $action = $_POST['action'] ?? null;
    $email = $_POST['Email'] ?? null;
    $password = $_POST['Pass'] ?? null;

    // register
    $fname = $_POST['fname'] ?? null;
    $mname = $_POST['mname'] ?? null;
    $lname = $_POST['lname'] ?? null;
    $dob = $_POST['birthday'] ?? null;
    $mail = $_POST['mail'] ?? null;
    $num = $_POST['contact'] ?? null;
    $sex = $_POST['sex'] ?? null;
    $civstat = $_POST['civilstat'] ?? null;
    $nationality = $_POST['nationality'] ?? null;
    $empstat = $_POST['employmentstat'] ?? null;
    $empl = $_POST['employer'] ?? null;
    $profession = $_POST['profession'] ?? null;
    $address = $_POST['address'] ?? null;
    $barangay = $_POST['barangay'] ?? null;
    $allergy = $_POST['allergy_description'] ?? null;
    $disease = $_POST['disease_description'] ?? null;

    // sign up
    $Name = $_POST['Name'] ?? null;
    $Pass = $_POST['Pass'] ?? null;
    $ConPass = $_POST['ConPass'] ?? null;

    $dbConfig = [
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'root',
        'dbname' => 'vaccination'
    ];

    $UserAuth = new UserAuth($dbConfig);
    $Register = new Register($dbConfig);

    if ($action === 'signin') {
        $result = $UserAuth->authenticate($email, $password);
    } elseif ($action === 'register') {
        $result = $Register->register_PersonalInfo(
            $fname,
            $mname,
            $lname,
            $dob,
            $mail,
            $num,
            $sex,
            $civstat,
            $nationality,
            $empstat,
            $empl,
            $profession,
            $address,
            $barangay,
            $allergies,
            $diseases
        );
    } else {
        $result = ["success" => false, "message" => "Invalid action"];
    }

    echo json_encode($result);

    $UserAuth->close();
}
