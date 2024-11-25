<?php
require_once 'DB_Connect.php';

session_start();
$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['feedback']) && $userId) {
    $feedback = $data['feedback'];
    $query = "INSERT INTO Feedback (user_id, feedback_text) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $userId, $feedback);

    $response = ["success" => false];
    if ($stmt->execute()) {
        $response["success"] = true;
    }
    echo json_encode($response);

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid input."]);
}

$conn->close();
?>