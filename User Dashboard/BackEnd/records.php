<?php
session_start();

// Database connection
$db = new PDO('mysql:host=localhost;dbname=shotsafe_data', 'username', 'password');

class RecordsManager {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function getVaccinationHistory() {
        // Get complete vaccination history with details
        $stmt = $this->db->prepare("
            SELECT 
                v.vaccine_id,
                v.vaccine_name,
                v.date_administered,
                v.dose_number,
                v.location,
                vs.total_doses,
                vs.recommended_interval
            FROM Vaccine v
            JOIN vaccine_schedule vs ON v.vaccine_name = vs.vaccine_name
            WHERE v.user_id = :user_id
            ORDER BY v.date_administered DESC
        ");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHealthInformation() {
        // Get user's health information
        $stmt = $this->db->prepare("
            SELECT 
                h.health_id,
                h.allergy_description,
                h.disease_description
            FROM Health h
            WHERE h.user_id = :user_id
        ");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateHealthInformation($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO Health (
                    user_id, 
                    allergy_description, 
                    disease_description
                ) VALUES (
                    :user_id,
                    :allergy_description,
                    :disease_description
                ) ON DUPLICATE KEY UPDATE
                    allergy_description = VALUES(allergy_description),
                    disease_description = VALUES(disease_description)
            ");

            $stmt->execute([
                'user_id' => $_SESSION['user_id'],
                'allergy_description' => $data['allergy_description'],
                'disease_description' => $data['disease_description']
            ]);

            return ['success' => true, 'message' => 'Health information updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getVaccinationSchedule() {
        // Get vaccination schedule with progress
        $stmt = $this->db->prepare("
            SELECT 
                vs.vaccine_name,
                vs.recommended_interval,
                vs.total_doses,
                vs.minimum_age,
                vs.maximum_age,
                COUNT(v.vaccine_id) as doses_completed
            FROM vaccine_schedule vs
            LEFT JOIN Vaccine v ON v.vaccine_name = vs.vaccine_name 
                AND v.user_id = :user_id
            GROUP BY 
                vs.vaccine_name,
                vs.recommended_interval,
                vs.total_doses,
                vs.minimum_age,
                vs.maximum_age
        ");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addVaccinationRecord($data) {
        try {
            // Validate the vaccination date
            if (strtotime($data['date_administered']) > strtotime('today')) {
                throw new Exception('Vaccination date cannot be in the future');
            }

            // Check if this dose number already exists for this vaccine
            $checkStmt = $this->db->prepare("
                SELECT COUNT(*) 
                FROM Vaccine 
                WHERE user_id = :user_id 
                    AND vaccine_name = :vaccine_name 
                    AND dose_number = :dose_number
            ");
            $checkStmt->execute([
                'user_id' => $_SESSION['user_id'],
                'vaccine_name' => $data['vaccine_name'],
                'dose_number' => $data['dose_number']
            ]);

            if ($checkStmt->fetchColumn() > 0) {
                throw new Exception('This dose has already been recorded');
            }

            // Insert new vaccination record
            $stmt = $this->db->prepare("
                INSERT INTO Vaccine (
                    user_id,
                    vaccine_name,
                    date_administered,
                    dose_number,
                    location
                ) VALUES (
                    :user_id,
                    :vaccine_name,
                    :date_administered,
                    :dose_number,
                    :location
                )
            ");

            $stmt->execute([
                'user_id' => $_SESSION['user_id'],
                'vaccine_name' => $data['vaccine_name'],
                'date_administered' => $data['date_administered'],
                'dose_number' => $data['dose_number'],
                'location' => $data['location']
            ]);

            return ['success' => true, 'message' => 'Vaccination record added successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function generateVaccinationCertificate() {
        // Get user's personal information and vaccination history
        $stmt = $this->db->prepare("
            SELECT 
                pi.fname,
                pi.mname,
                pi.lname,
                pi.birthday,
                pi.mail,
                pi.contact,
                v.vaccine_name,
                v.date_administered,
                v.dose_number,
                v.location
            FROM Personal_Info pi
            LEFT JOIN Vaccine v ON v.user_id = pi.personal_id
            WHERE pi.personal_id = :user_id
            ORDER BY v.date_administered DESC
        ");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    $recordsManager = new RecordsManager($db);
    $response = [];

    switch ($_GET['action']) {
        case 'get_vaccination_history':
            $response = $recordsManager->getVaccinationHistory();
            break;

        case 'get_health_info':
            $response = $recordsManager->getHealthInformation();
            break;

        case 'update_health_info':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $response = $recordsManager->updateHealthInformation($data);
            }
            break;

        case 'get_vaccination_schedule':
            $response = $recordsManager->getVaccinationSchedule();
            break;

        case 'add_vaccination':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $response = $recordsManager->addVaccinationRecord($data);
            }
            break;

        case 'generate_certificate':
            $response = $recordsManager->generateVaccinationCertificate();
            break;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?> 