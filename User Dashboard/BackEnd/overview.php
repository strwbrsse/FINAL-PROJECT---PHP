<?php
session_start();

// Database connection (adjust credentials as needed)
$db = new PDO('mysql:host=localhost;dbname=shotsafe_data', 'username', 'password');

class DashboardData {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function getVaccinationProgress() {
        // Get completed vaccines and total required doses
        $stmt = $this->db->prepare("
            SELECT 
                (SELECT COUNT(*) 
                 FROM Vaccine 
                 WHERE user_id = :user_id) as completed,
                (SELECT SUM(total_doses) 
                 FROM vaccine_schedule) as total
        ");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getNextAppointment() {
        // Get next upcoming appointment
        $stmt = $this->db->prepare("
            SELECT 
                appointment_date,
                appointment_time,
                vaccine_type,
                dose_number,
                location
            FROM appointments 
            WHERE user_id = :user_id 
                AND status = 'scheduled'
                AND appointment_date >= CURRENT_DATE 
            ORDER BY appointment_date ASC, appointment_time ASC
            LIMIT 1
        ");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getRemainingDoses() {
        // Calculate remaining doses based on schedule and completed vaccinations
        $stmt = $this->db->prepare("
            SELECT 
                (SELECT SUM(total_doses) FROM vaccine_schedule) - 
                (SELECT COUNT(*) FROM Vaccine WHERE user_id = :user_id) 
                as remaining
        ");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getRecentActivity() {
        // Get recent vaccination history and appointments
        $stmt = $this->db->prepare("
            (SELECT 
                'vaccination' as activity_type,
                CONCAT('Received ', vaccine_name, ' dose #', dose_number) as description,
                vaccination_date as activity_date
            FROM vaccination_history 
            WHERE user_id = :user_id)
            UNION
            (SELECT 
                'appointment' as activity_type,
                CONCAT('Scheduled ', vaccine_type, ' dose #', dose_number) as description,
                appointment_date as activity_date
            FROM appointments 
            WHERE user_id = :user_id)
            ORDER BY activity_date DESC 
            LIMIT 5
        ");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserInfo() {
        // Get user's personal information
        $stmt = $this->db->prepare("
            SELECT 
                CONCAT(fname, ' ', lname) as full_name,
                mail,
                contact
            FROM Personal_Info 
            WHERE personal_id = :user_id
        ");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    $dashboard = new DashboardData($db);
    $response = [];

    switch ($_GET['action']) {
        case 'vaccination_progress':
            $response = $dashboard->getVaccinationProgress();
            break;
        case 'next_appointment':
            $response = $dashboard->getNextAppointment();
            break;
        case 'remaining_doses':
            $response = $dashboard->getRemainingDoses();
            break;
        case 'recent_activity':
            $response = $dashboard->getRecentActivity();
            break;
        case 'user_info':
            $response = $dashboard->getUserInfo();
            break;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?> 