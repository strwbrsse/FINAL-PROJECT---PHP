<?php
require_once 'DB_Connect.php';

session_start();
$userId = $_SESSION['user_id'];

$query = "SELECT vaccine_name, date_booked, location FROM Vaccine WHERE user_id = ? AND date_booked >= CURDATE() ORDER BY date_booked";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}

echo json_encode($appointments);
$stmt->close();
$conn->close();
?>