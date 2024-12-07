<?php
session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

try {
    $userId = $_SESSION['user_id'];
    
    $query = "SELECT a.*, v.vaccine_name 
              FROM appointments a 
              JOIN vaccines v ON a.vaccine_id = v.id 
              WHERE a.user_id = ? AND a.date >= CURRENT_DATE 
              ORDER BY a.date ASC, a.time ASC 
              LIMIT 1";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'next_appointment' => [
                'date' => $row['date'],
                'time' => $row['time'],
                'vaccine_name' => $row['vaccine_name']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'next_appointment' => null
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch next appointment'
    ]);
}

$conn->close();
?>