<?php
require_once 'DB_Connect.php';

session_start();
$userId = $_SESSION['user_id'];

$query = "SELECT vaccine_name, date_booked FROM Vaccine WHERE user_id = ? AND date_booked > CURDATE() ORDER BY date_booked";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$reminders = [];
while ($row = $result->fetch_assoc()) {
    $reminders[] = "Reminder: Your " . $row['vaccine_name'] . " appointment is on " . $row['date_booked'];
}

echo json_encode($reminders);
$stmt->close();
$conn->close();
?>