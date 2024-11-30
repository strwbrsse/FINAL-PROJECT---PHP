<?php
require_once 'DB_Connect.php';

session_start();
header('Content-Type: application/json');

$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);

if ($userId && isset($data['fname'], $data['lname'], $data['birthday'], $data['mail'], $data['contact'], $data['address'], $data['sex'], $data['civilstat'], $data['employmentstat'])) {
    $fname = $data['fname'];
    $mname = $data['mname'] ?? null;
    $lname = $data['lname'];
    $birthday = $data['birthday'];
    $mail = $data['mail'];
    $contact = $data['contact'];
    $address = $data['address'];
    $barangay = $data['barangay'] ?? null;
    $sex = $data['sex'];
    $civilstat = $data['civilstat'];
    $employmentstat = $data['employmentstat'];
    $employer = $data['employer'] ?? null;
    $profession = $data['profession'] ?? null;

    $query = "UPDATE Personal_Info SET fname = ?, mname = ?, lname = ?, birthday = ?, mail = ?, contact = ?, address = ?, barangay = ?, sex = ?, civilstat = ?, employmentstat = ?, employer = ?, profession = ? WHERE personal_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssssssssss", $fname, $mname, $lname, $birthday, $mail, $contact, $address, $barangay, $sex, $civilstat, $employmentstat, $employer, $profession, $userId);

    $response = ["success" => false];
    if ($stmt->execute()) {
        $response["success"] = true;
    }
    echo json_encode($response);

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid input data."]);
}

$conn->close();
?>