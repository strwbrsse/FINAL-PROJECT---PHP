<?php
session_start();

// Database connection
$db = new PDO('mysql:host=localhost;dbname=shotsafe_data', 'username', 'password');

class AppointmentManager {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function getUpcomingAppointments() {
        $stmt = $this->db->prepare("
            SELECT 
                a.id,
                a.appointment_date,
                a.appointment_time,
                a.vaccine_type,
                a.dose_number,
                a.location,
                a.status
            FROM appointments a
            WHERE a.user_id = :user_id 
                AND a.status = 'scheduled'
                AND a.appointment_date >= CURRENT_DATE
            ORDER BY a.appointment_date ASC, a.appointment_time ASC
        ");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPastAppointments() {
        $stmt = $this->db->prepare("
            SELECT 
                a.id,
                a.appointment_date,
                a.appointment_time,
                a.vaccine_type,
                a.dose_number,
                a.location,
                a.status
            FROM appointments a
            WHERE a.user_id = :user_id 
                AND (a.appointment_date < CURRENT_DATE OR a.status != 'scheduled')
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
            LIMIT 10
        ");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createAppointment($data) {
        try {
            // Validate appointment date is in the future
            if (strtotime($data['appointment_date']) < strtotime('today')) {
                throw new Exception('Appointment date must be in the future');
            }

            $stmt = $this->db->prepare("
                INSERT INTO appointments (
                    user_id,
                    appointment_date,
                    appointment_time,
                    vaccine_type,
                    dose_number,
                    location,
                    status
                ) VALUES (
                    :user_id,
                    :appointment_date,
                    :appointment_time,
                    :vaccine_type,
                    :dose_number,
                    :location,
                    'scheduled'
                )
            ");

            $stmt->execute([
                'user_id' => $_SESSION['user_id'],
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $data['appointment_time'],
                'vaccine_type' => $data['vaccine_type'],
                'dose_number' => $data['dose_number'],
                'location' => $data['location']
            ]);

            return ['success' => true, 'message' => 'Appointment created successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateAppointmentStatus($appointmentId, $status) {
        try {
            $stmt = $this->db->prepare("
                UPDATE appointments 
                SET status = :status 
                WHERE id = :id AND user_id = :user_id
            ");

            $stmt->execute([
                'id' => $appointmentId,
                'status' => $status,
                'user_id' => $_SESSION['user_id']
            ]);

            return ['success' => true, 'message' => 'Appointment status updated'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getAvailableVaccines() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    vs.vaccine_name
                FROM vaccine_schedule vs
                LEFT JOIN Vaccine v ON v.vaccine_name = vs.vaccine_name 
                    AND v.user_id = :user_id
                GROUP BY vs.vaccine_name, vs.total_doses
                HAVING COUNT(v.vaccine_id) < vs.total_doses OR COUNT(v.vaccine_id) IS NULL
            ");
            
            $stmt->execute(['user_id' => $_SESSION['user_id']]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Debug log
            error_log('Available vaccines: ' . print_r($results, true));
            
            return $results;
        } catch (Exception $e) {
            error_log('Error getting available vaccines: ' . $e->getMessage());
            return [];
        }
    }

    public function getVaccinationLocations() {
        // You might want to store these in a separate table
        // For now, returning static locations
        return [
            'City Health Office - Main Branch',
            'City Health Office - North Branch',
            'City Health Office - South Branch',
            'Mobile Vaccination Center',
            'Central Hospital'
        ];
    }
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    $appointmentManager = new AppointmentManager($db);
    $response = [];

    switch ($_GET['action']) {
        case 'get_upcoming':
            $response = $appointmentManager->getUpcomingAppointments();
            break;

        case 'get_past':
            $response = $appointmentManager->getPastAppointments();
            break;

        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $response = $appointmentManager->createAppointment($data);
            }
            break;

        case 'update_status':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $response = $appointmentManager->updateAppointmentStatus(
                    $data['appointment_id'],
                    $data['status']
                );
            }
            break;

        case 'get_available_vaccines':
            $response = $appointmentManager->getAvailableVaccines();
            break;

        case 'get_locations':
            $response = $appointmentManager->getVaccinationLocations();
            break;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?> 