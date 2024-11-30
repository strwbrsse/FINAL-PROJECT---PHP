<?php
require_once 'DB_Connect.php';

session_start();
header('Content-Type: application/json');

$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['vaccine_name'], $data['date_booked'], $data['location']) && $userId) {
    $vaccineName = $data['vaccine_name'];
    $dateBooked = $data['date_booked'];
    $location = $data['location'];

    $query = "INSERT INTO Vaccine (user_id, vaccine_name, date_booked, location, dose_number) VALUES (?, ?, ?, ?, 1)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isss", $userId, $vaccineName, $dateBooked, $location);

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