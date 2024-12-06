<?php
session_start();
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Log session data
    error_log('Session data: ' . print_r($_SESSION, true));

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    $userId = $_SESSION['user_id'];
    error_log('User ID: ' . $userId);

    try {
        // Database connection with error logging
        $db = new PDO('mysql:host=localhost;dbname=shotsafe_data', 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        error_log('Database connection successful');
    } catch (PDOException $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        throw new Exception('Database connection failed');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] === 'create') {
        // Get and log POST data
        $rawData = file_get_contents('php://input');
        error_log('Raw POST data: ' . $rawData);
        
        $data = json_decode($rawData, true);
        error_log('Decoded appointment data: ' . print_r($data, true));

        // Validate data
        if (!$data) {
            throw new Exception('Invalid JSON data received');
        }

        // Required fields check with detailed logging
        $required = ['vaccine_type', 'appointment_date', 'appointment_time'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                error_log("Missing required field: $field");
                throw new Exception("Missing required field: $field");
            }
        }

        try {
            // Begin transaction
            $db->beginTransaction();
            error_log('Transaction started');

            // Create appointment
            $stmt = $db->prepare("
                INSERT INTO appointments (
                    user_id,
                    vaccine_type,
                    appointment_date,
                    appointment_time,
                    status,
                    created_at
                ) VALUES (
                    :user_id,
                    :vaccine_type,
                    :appointment_date,
                    :appointment_time,
                    'scheduled',
                    NOW()
                )
            ");

            $params = [
                'user_id' => $userId,
                'vaccine_type' => $data['vaccine_type'],
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $data['appointment_time']
            ];
            error_log('Executing query with params: ' . print_r($params, true));

            $stmt->execute($params);
            
            // Commit transaction
            $db->commit();
            error_log('Transaction committed successfully');

            echo json_encode([
                'success' => true,
                'message' => 'Appointment created successfully'
            ]);
        } catch (PDOException $e) {
            // Rollback transaction on error
            $db->rollBack();
            error_log('Database error: ' . $e->getMessage());
            throw new Exception('Database error: ' . $e->getMessage());
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        switch ($_GET['action']) {
            case 'get_upcoming':
                echo json_encode([
                    'success' => true,
                    'data' => getUpcomingAppointments($userId)
                ]);
                break;
            
            case 'get_next':
                echo json_encode([
                    'success' => true,
                    'data' => getNextAppointment($userId)
                ]);
                break;
            
            case 'get_doses':
                echo json_encode([
                    'success' => true,
                    'data' => getUpcomingDoses($userId)
                ]);
                break;
        }
    }

} catch (Exception $e) {
    error_log('Appointment creation error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'details' => 'Check server logs for more information'
    ]);
}

function getUpcomingAppointments($userId) {
    global $db;  // Add this to access the database connection
    $stmt = $db->prepare("
        SELECT 
            id,
            vaccine_type,
            appointment_date,
            appointment_time,
            status
        FROM appointments 
        WHERE user_id = :user_id 
        AND appointment_date >= CURRENT_DATE
        AND status = 'scheduled'
        ORDER BY appointment_date ASC, appointment_time ASC
    ");
    
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll();
}

function getNextAppointment($userId) {
    global $db;  // Add this to access the database connection
    $stmt = $db->prepare("
        SELECT 
            id,
            vaccine_type,
            appointment_date,
            appointment_time,
            status
        FROM appointments 
        WHERE user_id = :user_id 
        AND appointment_date >= CURRENT_DATE
        AND status = 'scheduled'
        ORDER BY appointment_date ASC, appointment_time ASC
        LIMIT 1
    ");
    
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetch();
}

function getUpcomingDoses($userId) {
    global $db;  // Add this to access the database connection
    $stmt = $db->prepare("
        SELECT 
            vs.vaccine_name,
            COUNT(v.vaccine_id) as doses_received,
            vs.total_doses,
            CASE 
                WHEN COUNT(v.vaccine_id) < vs.total_doses THEN vs.total_doses - COUNT(v.vaccine_id)
                ELSE 0 
            END as doses_remaining
        FROM vaccine_schedule vs
        LEFT JOIN vaccine v ON v.vaccine_name = vs.vaccine_name AND v.name_id = :user_id
        GROUP BY vs.vaccine_name, vs.total_doses
        HAVING doses_remaining > 0
        ORDER BY vs.vaccine_name
    ");
    
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll();
}
?>
