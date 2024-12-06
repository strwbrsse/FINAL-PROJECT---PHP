<?php
session_start();

$db = mysqli_connect('localhost', 'username', 'password', 'shotsafe_data');

class DashboardData {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function getVaccinationProgress() {
        $user_id = $_SESSION['user_id'];
        $query = "
            SELECT 
                (SELECT COUNT(*) 
                 FROM Vaccine 
                 WHERE user_id = ?) as completed,
                (SELECT SUM(total_doses) 
                 FROM vaccine_schedule) as total
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getNextAppointment() {
        $user_id = $_SESSION['user_id'];
        $query = "
            SELECT 
                appointment_date,
                appointment_time,
                vaccine_type,
                dose_number,
                location
            FROM appointments 
            WHERE user_id = ? 
                AND status = 'scheduled'
                AND appointment_date >= CURRENT_DATE 
            ORDER BY appointment_date ASC, appointment_time ASC
            LIMIT 1
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getRemainingDoses() {
        $user_id = $_SESSION['user_id'];
        $query = "
            SELECT 
                (SELECT SUM(total_doses) FROM vaccine_schedule) - 
                (SELECT COUNT(*) FROM Vaccine WHERE user_id = ?) 
                as remaining
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getRecentActivity() {
        $user_id = $_SESSION['user_id'];
        $query = "
            (SELECT 
                'vaccination' as activity_type,
                CONCAT('Received ', vaccine_name, ' dose #', dose_number) as description,
                vaccination_date as activity_date
            FROM vaccination_history 
            WHERE user_id = ?)
            UNION
            (SELECT 
                'appointment' as activity_type,
                CONCAT('Scheduled ', vaccine_type, ' dose #', dose_number) as description,
                appointment_date as activity_date
            FROM appointments 
            WHERE user_id = ?)
            ORDER BY activity_date DESC 
            LIMIT 5
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getUserInfo() {
        $user_id = $_SESSION['user_id'];
        $query = "
            SELECT 
                CONCAT(fname, ' ', lname) as full_name,
                mail,
                contact
            FROM Personal_Info 
            WHERE personal_id = ?
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
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