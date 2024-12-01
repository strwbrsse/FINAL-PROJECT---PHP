<?php

require_once 'UserAuth.php';
require_once 'Register.php';
require_once 'SignUp.php';

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
    $allergies = $_POST['allergy_description'] ?? null;
    $diseases = $_POST['disease_description'] ?? null;
    $allergyCheck = $_POST['allergy_check'] ?? null;
    $diseaseCheck = $_POST['disease_check'] ?? null;

    //sign up
    $username = $_POST['Name'] ?? null;
    $password = $_POST['Pass'] ?? null;
    $confirmPassword = $_POST['ConPass'] ?? null;

    $dbConfig = [
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'root',
        'dbname' => 'vaccination'
    ];

    $UserAuth = new UserAuth($dbConfig);
    $Register = new UserReg($dbConfig);
    $SignUp = new UserSignUp($dbConfig);

    if ($action === 'signin') {
        $result = $UserAuth->authenticate($email, $password);
    } elseif ($action === 'register') {
        $result = $Register->register_PersonalInfo(
            $fname, $mname, $lname, $dob, $mail, $num, $sex,
            $civstat, $nationality, $empstat, $empl, $profession,
            $address, $barangay, $allergies, $diseases,
            $allergyCheck, $diseaseCheck
        );
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    } elseif ($action === 'signup') {
        $result = $SignUp->signUp($username, $password, $confirmPassword);
    } else {
        $result = ["success" => false, "message" => "Invalid action"];
    }

    echo json_encode($result);

    $UserAuth->close();
    $Register->close();
    $SignUp->close();
}
