<?php
require_once 'DB_Connect.php';

session_start();
$userId = $_SESSION['user_id'];

$query = "SELECT vaccine_name, date_administered, dose_number, location FROM Vaccine WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$history = [];
while ($row = $result->fetch_assoc()) {
    $history[] = $row;
}

echo json_encode($history);
$stmt->close();
$conn->close();
?>