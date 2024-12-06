<?php
session_start();

try {
    $db = new PDO('mysql:host=localhost;dbname=shotsafe_data', 'username', 'password', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


class AppointmentManager {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function validateCSRFToken($token) {
        if (empty($token) || $token !== $_SESSION['csrf_token']) {
            throw new Exception('CSRF token mismatch');
        }
    }

    public function getUpcomingAppointments($userId) {
        $stmt = $this->db->prepare("
            SELECT 
                id, appointment_date, appointment_time, vaccine_type, dose_number, location, status
            FROM appointments
            WHERE user_id = :user_id AND status = 'scheduled' AND appointment_date >= CURRENT_DATE
            ORDER BY appointment_date ASC, appointment_time ASC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getPastAppointments($userId) {
        $stmt = $this->db->prepare("
            SELECT 
                id, appointment_date, appointment_time, vaccine_type, dose_number, location, status
            FROM appointments
            WHERE user_id = :user_id 
              AND (appointment_date < CURRENT_DATE OR status != 'scheduled')
            ORDER BY appointment_date DESC, appointment_time DESC
            LIMIT 10
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function createAppointment($data, $userId) {
        if (strtotime($data['appointment_date']) < strtotime('today')) {
            throw new Exception('Appointment date must be in the future');
        }

        $stmt = $this->db->prepare("
            INSERT INTO appointments (
                user_id, appointment_date, appointment_time, vaccine_type, dose_number, status
            ) VALUES (
                :user_id, :appointment_date, :appointment_time, :vaccine_type, :dose_number, 'scheduled'
            )
        ");
        $stmt->execute([
            'user_id' => $userId,
            'appointment_date' => $data['appointment_date'],
            'appointment_time' => $data['appointment_time'],
            'vaccine_type' => $data['vaccine_type'],
            'dose_number' => $data['dose_number']
        ]);

        return ['success' => true, 'message' => 'Appointment created successfully'];
    }

    public function updateAppointmentStatus($appointmentId, $status, $userId) {
        $stmt = $this->db->prepare("
            UPDATE appointments 
            SET status = :status 
            WHERE id = :id AND user_id = :user_id
        ");
        $stmt->execute([
            'id' => $appointmentId,
            'status' => $status,
            'user_id' => $userId
        ]);
        return ['success' => true, 'message' => 'Appointment status updated'];
    }

    public function getAvailableVaccines($userId) {
        $stmt = $this->db->prepare("
            SELECT 
                vs.vaccine_name
            FROM vaccine_schedule vs
            LEFT JOIN Vaccine v ON v.vaccine_name = vs.vaccine_name 
                AND v.name_id = :user_id
            GROUP BY vs.vaccine_name, vs.total_doses
            HAVING COUNT(v.vaccine_id) < vs.total_doses OR COUNT(v.vaccine_id) IS NULL
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    $appointmentManager = new AppointmentManager($db);
    $response = [];
    $userId = $_SESSION['user_id'] ?? null;

    if (!$userId) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'User not authenticated']);
        exit;
    }

    try {
        switch ($_GET['action']) {
            case 'get_upcoming':
                $response = ['success' => true, 'data' => $appointmentManager->getUpcomingAppointments($userId)];
                break;

            case 'get_past':
                $response = ['success' => true, 'data' => $appointmentManager->getPastAppointments($userId)];
                break;

            case 'create':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $data = json_decode(file_get_contents('php://input'), true);
                    $appointmentManager->validateCSRFToken($data['csrf_token']);
                    $response = $appointmentManager->createAppointment($data, $userId);
                }
                break;

            case 'update_status':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $data = json_decode(file_get_contents('php://input'), true);
                    $appointmentManager->validateCSRFToken($data['csrf_token']);
                    $response = $appointmentManager->updateAppointmentStatus($data['appointment_id'], $data['status'], $userId);
                }
                break;

            case 'get_available_vaccines':
                $response = ['success' => true, 'data' => $appointmentManager->getAvailableVaccines($userId)];
                break;

            default:
                throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        http_response_code(400);
        $response = ['success' => false, 'error' => $e->getMessage()];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>
